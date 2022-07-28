<?php

namespace App\Controllers
{

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
        /**
         * @var ILogger logger
         */
        private ILogger $logger;

        /**
         * Profile constructor.
         * @param ILogger $logger
         */
        public function __construct(ILogger $logger)
        {
            parent::__construct();
            $this->logger = $logger;
        }

        /**
         * Load scripts and view of web page
         * @param string|null $id id of user
         * @return Response
         */
        public function getView(?string $id = null): Response
        {

            try
            {
                $user = is_null($id) ? $_SESSION['USER_DATA'] : User::loadUserById((int)$id);

                $this->addCssStyle('css-profil.css');
                $this->addJsScript('js-profil.js');

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));

                $titleWebPage = $user->getName('full');
                $cmdFriend = $this->getViewCmdFriend($user);
                $profileContent = $this->getViewContentProfile($user);
                $content = self::render('profil.view-profil', compact('user', "cmdFriend", "profileContent"));

                $view = $this->render('templates.template', compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content'));

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

            $me = $_SESSION['USER_DATA'];

            if ($me->equals($user))
            {
                return $this->render('profil.cmd-edit-profile');
            }
            else if (!$me->equals($user) && !$me->isFriend($user) && !$me->isFriendWait($user))
            {
                return $this->render('profil.cmd-no-friend');
            }
            else if (!$me->equals($user) && $me->isFriend($user))
            {
                return $this->render('profil.cmd-friend');
            }
            else if (!$me->equals($user) && $me->isFriendWaitFromMe($user))
            {
                return $this->render('profil.cmd-request-send');
            }
            else if (!$me->equals($user) && $me->isFriendWaitFromUser($user))
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
            $me = $_SESSION['USER_DATA'];
            if ($me->equals($user) || $me->isFriend($user))
            {
                $friends = $user->getFriends();
                return $this->render('profil.friends-list', compact('user', 'friends'));
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
            $me = $_SESSION['USER_DATA'];
            if ($me->equals($user))
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
            $me = $_SESSION['USER_DATA'];
            if ($me->equals($user) || $me->isFriend($user))
            {
                $participation = $user->getEventsParticipation(1);
                $organisation = $user->getEventsOrganisation(1);
            }
            else
            {
                $participation = $user->getEventsParticipation();
                $organisation = $user->getEventsOrganisation();

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
            $me = $_SESSION['USER_DATA'];
            $emitter = Emitter::getInstance();

            if (!$me->equals($user) && !$user->isFriend($me) && !$user->isFriendWait($me))
            {
                if ($me->sendFriendRequest($user))
                {
                    $emitter->emit('user.friend.request', $user, $me);
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
            $me = $_SESSION['USER_DATA'];
            $emitter = Emitter::getInstance();
            if (!$me->equals($user) && $user->isFriendWait($me))
            {
                if ($me->unsetFriendRequest($user))
                {
                    $emitter->emit('user.friend.unrequest', $user, $me);
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
            $me = $_SESSION['USER_DATA'];
            $emitter = Emitter::getInstance();
            if (!$me->equals($user) && $me->isFriendWait($user))
            {
                if ($me->acceptFriend($user))
                {
                    $emitter->emit('user.friend.accept', $user, $me);
                    return true;
                }
            }
            else if ($me->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::loadUserById((int)$_POST['friendId']);
                if ($me->isFriendWait($friend) && $me->acceptFriend($friend))
                {
                    $emitter->emit('user.friend.accept', $friend, $me);
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
            $me = $_SESSION['USER_DATA'];
            if (!$me->equals($user) && $me->isFriendWait($user))
            {
                if ($me->refuseFriend($user))
                {
                    return true;
                }
            }
            else if ($me->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::loadUserById((int)$_POST['friendId']);
                if ($me->isFriendWait($friend) && $me->refuseFriend($friend))
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
            $me = $_SESSION['USER_DATA'];
            $friend = $user;
            $emitter = Emitter::getInstance();
            if (!$me->equals($user) && $me->isFriend($user))
            {
                if ($me->unsetFriend($user))
                {
                    $emitter->emit('user.friend.delete', $friend, $me);
                    return true;
                }
            }
            else if ($me->equals($user) && isset($_POST['friendId']) && !empty($_POST['friendId']))
            {
                $friend = User::loadUserById((int)$_POST['friendId']);
                if ($me->isFriend($friend) && $me->unsetFriend($friend))
                {
                    $emitter->emit('user.friend.delete', $friend, $me);
                    return true;
                }
            }
            return false;

        }
    }
}