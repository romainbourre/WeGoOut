<?php

namespace App\Controllers
{

    use App\Authentication\AuthenticationContext;
    use App\Librairies\Emitter;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Entities\User;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Response;

    /**
     * Class Profile
     * Represent user profile web page
     * @package App\Controllers
     */
    class ProfileController extends AppController
    {
        public function __construct(private readonly ILogger $logger, private readonly AuthenticationContext $authenticationGateway)
        {
            parent::__construct();
        }

        public function getView(?string $id = null): Response
        {

            try
            {
                $connectedUser = $this->authenticationGateway->getConnectedUser();
                $userToLoadProfile = is_null($id) ? $connectedUser : User::loadUserById((int)$id);

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

        /**
         * Ajax router
         * @param string $action
         * @param User $user
         * @return string|null view of request
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
         * @throws Exception
         */
        public function getAjax(string $action, User $user): ?string
        {

            switch ($action)
            {

                case "content.update":
                    return $this->getViewContentProfile($user);
                case "friend.update":
                    return $this->getViewCmdFriend($user);
                case "friend.send":
                    return $this->sendRequestFriend($user);
                case "friend.cancel.send":
                    return $this->unsetRequestFriend($user);
                case "friend.accept":
                    return $this->acceptFriend($user);
                case "friend.refuse":
                    return $this->refuseFriend($user);
                case "friend.delete":
                    return $this->deleteFriend($user);
            }

            return null;
        }

        /**
         * Load view of content profile of user
         * @param User $user
         * @return string
         */
        public function getViewContentProfile(User $user): string
        {
            $friendsList = $this->getViewFriendsList($user);
            $friendsRequestList = $this->getViewFriendsRequestList($user);
            $contentProfileEvents = $this->getViewContentProfileEvents($user);
            return $this->render('profil.profile-content', compact('user', 'friendsList', 'friendsRequestList', 'contentProfileEvents'));
        }

        /**
         * Load view of panel control event
         * @param User $user
         * @return null|string
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
         * @param User $user
         * @return null|string
         */
        public function getViewContentProfileEvents(User $user): ?string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if ($connectedUser->equals($user) || $connectedUser->isFriend($user))
            {
                $participation = $user->getEventsParticipation(1);
                $organisation = $user->getOrganizedEvents($connectedUser, 1);
            }
            else
            {
                $participation = $user->getEventsParticipation();
                $organisation = $user->getOrganizedEvents($connectedUser);
            }
            return $this->render("profil.content-profile-events", compact('user', 'participation', 'organisation'));
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
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
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
                $friend = User::loadUserById((int)$_POST['friendId']);
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
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
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
                $friend = User::loadUserById((int)$_POST['friendId']);
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
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
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
                $friend = User::loadUserById((int)$_POST['friendId']);
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