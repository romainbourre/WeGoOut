<?php

namespace WebApp\Controllers
{

    use Business\Entities\User;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\UserDeletedException;
    use Business\Exceptions\UserNotExistException;
    use Business\Exceptions\UserSignaledException;
    use Business\Ports\AuthenticationContextInterface;
    use Exception;
    use Slim\Psr7\Response;
    use System\Logging\ILogger;
    use WebApp\Exceptions\NotConnectedUserException;
    use WebApp\Librairies\Emitter;

    /**
     * Class Profile
     * Represent user profile web page
     * @package App\Controllers
     */
    class ProfileController extends AppController
    {
        public function __construct(
            private readonly ILogger $logger,
            private readonly AuthenticationContextInterface $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * @throws DatabaseErrorException
         * @throws NotConnectedUserException
         * @throws Exception
         */
        public function getView(?string $id = null): Response
        {

            try
            {
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
                $userToLoadProfile = is_null($id) ? $connectedUser : User::load((int)$id);

                $this->addCssStyle('css-profil.css');
                $this->addJsScript('js-profil.js');

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));

                $titleWebPage = $userToLoadProfile->getName('full');
                $cmdFriend = $this->getViewCmdFriend($userToLoadProfile);
                $profileContent = $this->getViewContentProfile($userToLoadProfile);
                $content = self::render('profil.view-profil', compact('userToLoadProfile', "cmdFriend", "profileContent"));

                $view = $this->render('templates.template', compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content', 'connectedUser'));

                return $this->ok($view);

            }
            catch (UserNotExistException | UserSignaledException | UserDeletedException $e)
            {
                $this->logger->logError($e->getMessage());
                return $this->internalServerError()->withRedirectTo('/profile');
            }
        }

        /**est
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
         * @throws Exception
         */
        public function getAjax(string $action, User $user): ?string
        {
            return match ($action) {
                "content.update" => $this->getViewContentProfile($user),
                "friend.update" => $this->getViewCmdFriend($user),
                "friend.send" => $this->sendRequestFriend($user),
                "friend.cancel.send" => $this->unsetRequestFriend($user),
                "friend.accept" => $this->acceptFriend($user),
                "friend.refuse" => $this->refuseFriend($user),
                "friend.delete" => $this->deleteFriend($user),
                default => null,
            };
        }

        /**
         * Load view of content profile of user
         * @param User $userThatLoadProfile
         * @return string
         * @throws Exception
         */
        public function getViewContentProfile(User $userThatLoadProfile): string
        {
            $friendsList = $this->getViewFriendsList($userThatLoadProfile);
            $friendsRequestList = $this->getViewFriendsRequestList($userThatLoadProfile);
            $contentProfileEvents = $this->getViewContentProfileEvents($userThatLoadProfile);
            return $this->render(
                'profil.profile-content',
                compact('userThatLoadProfile', 'friendsList', 'friendsRequestList', 'contentProfileEvents')
            );
        }

        /**
         * Load view of panel control event
         * @param User $user
         * @return null|string
         * @throws Exception
         */
        public function getViewCmdFriend(User $user): ?string
        {

            $connectedUser = $this->authenticationGateway->getConnectedUser();

            if ($connectedUser->equals($user))
            {
                return $this->render('profil.cmd-edit-profile');
            }
            elseif (!$connectedUser->equals($user) && !$connectedUser->isFriend($user) && !$connectedUser->isFriendWait($user))
            {
                return $this->render('profil.cmd-no-friend');
            }
            elseif (!$connectedUser->equals($user) && $connectedUser->isFriend($user))
            {
                return $this->render('profil.cmd-friend');
            }
            elseif (!$connectedUser->equals($user) && $connectedUser->isFriendWaitFromMe($user))
            {
                return $this->render('profil.cmd-request-send');
            }
            elseif (!$connectedUser->equals($user) && $connectedUser->isFriendWaitFromUser($user))
            {
                return $this->render('profil.cmd-choose-friend');
            }

            return null;

        }

        /**
         * Load view of friends list of the user
         * @param User $user
         * @return null|string
         * @throws Exception
         */
        public function getViewFriendsList(User $user): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if ($connectedUser->equals($user) || $connectedUser->isFriend($user))
            {
                $friends = $user->getFriends();
                return $this->render('profil.friends-list', compact('user', 'friends', 'connectedUser'));
            }
            return null;
        }

        /**
         * Load view of friends request of the user
         * @param User $user
         * @return null|string
         * @throws UserNotExistException
         */
        public function getViewFriendsRequestList(User $user): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if ($connectedUser->equals($user))
            {
                $users = $user->getFriendsASkMe();
                return $this->render('profil.friends-request-list', compact('users'));
            }
            return null;
        }

        /**
         * Load view of event list of the user
         * @param User $userThatLoadProfile
         * @return null|string
         * @throws Exception
         */
        public function getViewContentProfileEvents(User $userThatLoadProfile): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if ($connectedUser->equals($userThatLoadProfile) || $connectedUser->isFriend($userThatLoadProfile)) {
                $participation = $userThatLoadProfile->getEventsWhichUserParticipate(1);
                $organisation = $userThatLoadProfile->getEventsWhichUserOrganize($connectedUser, 1);
            } else {
                $participation = $userThatLoadProfile->getEventsWhichUserParticipate();
                $organisation = $userThatLoadProfile->getEventsWhichUserOrganize($connectedUser);
            }
            return $this->render(
                "profil.content-profile-events",
                compact('userThatLoadProfile', 'participation', 'organisation')
            );
        }

        /**
         * Send friend request to the user
         * @param User $user
         * @return bool
         * @throws Exception
         */
        public function sendRequestFriend(User $user): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            $emitter = Emitter::getInstance();
            if (!$connectedUser->equals($user) && !$user->isFriend($connectedUser) && !$user->isFriendWait($connectedUser))
            {
                if ($connectedUser->sendFriendRequest($user))
                {
                    $emitter->emit('user.friend.request', $user, $connectedUser);
                    return true;
                }
            }
            return false;
        }

        /**
         * Unset friend request to the user
         * @param User $user
         * @return bool
         * @throws Exception
         */
        public function unsetRequestFriend(User $user): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            $emitter = Emitter::getInstance();
            if (!$connectedUser->equals($user) && $user->isFriendWait($connectedUser))
            {
                if ($connectedUser->unsetFriendRequest($user))
                {
                    $emitter->emit('user.friend.unrequest', $user, $connectedUser);
                    return true;
                }
            }
            return false;
        }

        /**
         * Accept friend request of an user for the user
         * @param User $user
         * @return bool
         * @throws Exception
         */
        public function acceptFriend(User $user): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            $emitter = Emitter::getInstance();
            if (!$connectedUser->equals($user) && $connectedUser->isFriendWait($user))
            {
                if ($connectedUser->acceptFriend($user))
                {
                    $emitter->emit('user.friend.accept', $user, $connectedUser);
                    return true;
                }
            }
            elseif ($connectedUser->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::load((int)$_POST['friendId']);
                if ($connectedUser->isFriendWait($friend) && $connectedUser->acceptFriend($friend))
                {
                    $emitter->emit('user.friend.accept', $friend, $connectedUser);
                    return true;
                }
            }
            return false;
        }

        /**
         * Refuse friend request of an user for the user
         * @param User $user
         * @return bool
         */
        public function refuseFriend(User $user): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if (!$connectedUser->equals($user) && $connectedUser->isFriendWait($user))
            {
                if ($connectedUser->refuseFriend($user))
                {
                    return true;
                }
            }
            elseif ($connectedUser->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::load((int)$_POST['friendId']);
                if ($connectedUser->isFriendWait($friend) && $connectedUser->refuseFriend($friend))
                {
                    return true;
                }
            }
            return false;
        }

        /**
         * Delete a friend of the user
         * @param User $user
         * @return bool
         * @throws Exception
         */
        public function deleteFriend(User $user): bool
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            $friend = $user;
            $emitter = Emitter::getInstance();
            if (!$connectedUser->equals($user) && $connectedUser->isFriend($user))
            {
                if ($connectedUser->unsetFriend($user))
                {
                    $emitter->emit('user.friend.delete', $friend, $connectedUser);
                    return true;
                }
            }
            elseif ($connectedUser->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::load((int)$_POST['friendId']);
                if ($connectedUser->isFriend($friend) && $connectedUser->unsetFriend($friend))
                {
                    $emitter->emit('user.friend.delete', $friend, $connectedUser);
                    return true;
                }
            }
            return false;
        }
    }
}