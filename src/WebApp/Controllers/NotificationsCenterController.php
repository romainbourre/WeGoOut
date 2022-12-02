<?php

namespace WebApp\Controllers
{

    use Business\Entities\Notifications;
    use Business\Ports\AuthenticationContextInterface;

    /**
     * Class NotificationsCenter
     * Represent notification center of user
     * @package App\Controllers
     */
    class NotificationsCenterController extends AppController
    {
        public function __construct(private readonly AuthenticationContextInterface $authenticationGateway)
        {
            parent::__construct();
        }

        /**
         * Load js and css script of notifications center
         */
        public function getView()
        {
            $this->addJsScript('js-notifications.js');
            $this->addCssStyle('css-notifications.css');
        }

        /**
         * Load view of notifications center
         * @return string
         */
        private function getViewNotifications(): string
        {

            $connectedUser = $this->authenticationGateway->getConnectedUser();

            // LOAD NOTIFICATIONS CENTER
            $notifications = Notifications::manager($connectedUser)->load(100);

            $notificationsContent = "";
            $_SESSION['UNREAD_NOTIFICATIONS'] = array();

            foreach ($notifications as $notification)
            {

                if ($this->compose($notification))
                {

                    // LOAD VIEW FOR EACH NOTIFICATION
                    $notificationsContent .= $this->render('notifications.view-notification', compact('notification'));
                    if ($notification->isUnread()) $_SESSION['UNREAD_NOTIFICATIONS'][] = $notification;

                }

            }

            // LOAD GLOBAL VIEW OF NOTIFICATIONS CENTER
            if (!empty($notificationsContent)) $notificationsContent = $this->render('notifications.view-notifications', compact('notificationsContent'));
            else $notificationsContent = $this->render('notifications.view-no-notifications');

            return $notificationsContent;

        }

        /**
         * Find signification of the notification
         * @param Notifications $notification
         * @return bool
         */
        private function compose(Notifications &$notification): bool
        {

            switch ($notification->getType())
            {

                /**
                 * Event notification
                 */
                case Notifications::TYPE_EVENT:
                    $notification->icon = "event";
                    switch ($notification->getCategory())
                    {

                        /**
                         * An user send a participant request to an event
                         */
                        case Notifications::CATEGORY_EVENT_REQUEST:
                            $notification->setMessage(
                                $notification->getTargetUser(
                                )->firstname . " demande a participer à " . $notification->getTargetEvent()->getTitle()
                            );
                            $notification->setAction("/events/{$notification->getTargetEvent()->getID()}");
                            return true;

                        /**
                         * An creator or organiser accept a participant request to an event
                         */
                        case Notifications::CATEGORY_EVENT_ACCEPT:
                            $notification->setMessage(
                                $notification->getTargetUser(
                                )->firstname . " a accepté votre demande de participation à " . $notification->getTargetEvent(
                                )->getTitle()
                            );
                            $notification->setAction("/events/{$notification->getTargetEvent()->getID()}");
                            return true;

                        /**
                         * An user subscribe to an event
                         */
                        case Notifications::CATEGORY_EVENT_SUBSCRIBE:
                            $notification->setMessage(
                                $notification->getTargetUser(
                                )->firstname . " participe à " . $notification->getTargetEvent()->getTitle()
                            );
                            $notification->setAction("/events/{$notification->getTargetEvent()->getID()}");
                            return true;

                        /**
                         * An user un-subscribe from an event
                         */
                        case Notifications::CATEGORY_EVENT_UNSUBSCRIBE:
                            $notification->setMessage(
                                $notification->getTargetUser(
                                )->firstname . " ne participe plus à " . $notification->getTargetEvent()->getTitle()
                            );
                            $notification->setAction("/events/{$notification->getTargetEvent()->getID()}");
                            return true;

                        /**
                         * An user is invited
                         */
                        case Notifications::CATEGORY_EVENT_SEND_INVITATION:
                            $notification->setMessage(
                                $notification->getTargetUser()->getName(
                                ) . " vous invite à son évènement " . $notification->getTargetEvent()->getTitle()
                            );
                            $notification->setAction("/events/{$notification->getTargetEvent()->getID()}");
                            return true;

                        /**
                         * Notification without category but with message
                         */
                        default:
                            if (!is_null($notification->getMessage()) && !empty($notification->getMessage())) return true;
                            else return false;

                    }

                /**
                 * User notification
                 */
                case Notifications::TYPE_USER:
                    $notification->icon = "person_add";
                    switch ($notification->getCategory())
                    {

                        /**
                         * An user send a friend request
                         */
                        case Notifications::CATEGORY_USER_REQUEST:
                            $notification->setMessage(
                                $notification->getTargetUser()->firstname . " vous a envoyé une demande d'ami"
                            );
                            $notification->setAction("/profile");
                            return true;

                        /**
                         * An user accept a friend request
                         */
                        case Notifications::CATEGORY_USER_ACCEPT:
                            $notification->setMessage(
                                $notification->getTargetUser()->firstname . " a accepté votre demande d'ami"
                            );
                            $notification->setAction("/profile/{$notification->getTargetUser()->getID()}");
                            return true;

                        /**
                         * Notification without category but with message
                         */
                        default:
                            if (!is_null($notification->getMessage()) && !empty($notification->getMessage())) return true;
                            else return false;

                    }

                /**
                 * Publication notification
                 */
                case Notifications::TYPE_PUBLICATION:
                    $notification->icon = "comment";
                    switch ($notification->getCategory())
                    {

                        /**
                         * An user leave a publication in an event
                         */
                        case Notifications::CATEGORY_PUBLICATION_PUBLICATION:
                            $event = $notification->getTargetEvent();
                            $notification->setMessage(
                                $notification->getTargetUser()->firstname . " a publié dans " . $event->getTitle()
                            );
                            $notification->setAction("/events/{$event->getID()}");
                            return true;

                        default:
                            if (!is_null($notification->getMessage()) && !empty($notification->getMessage())) return true;
                            else return false;

                    }

                /**
                 * Notification without category but with message
                 */
                case Notifications::TYPE_REVIEW:
                    $notification->icon = "stars";
                    switch ($notification->getCategory())
                    {

                        /**
                         * An user leave a review in an event
                         */
                        case Notifications::CATEGORY_REVIEW_REVIEW:
                            $event = $notification->getTargetEvent();
                            $notification->setMessage(
                                $notification->getTargetUser(
                                )->firstname . " a laissé un avis dans " . $event->getTitle()
                            );
                            $notification->setAction("/events/{$event->getID()}");
                            return true;

                        /**
                         * Notification without category but with message
                         */
                        default:
                            if (!is_null($notification->getMessage()) && !empty($notification->getMessage())) return true;
                            else return false;

                    }
                    break;

                /**
                 * Enjoy notification
                 */
                case Notifications::TYPE_ENJOY:
                    $notification->icon = "sentiment_very_satisfied";
                    switch ($notification->getCategory())
                    {

                        /**
                         * a user has just registered
                         */
                        case Notifications::CATEGORY_ENJOY_WELCOME:
                            $user = $notification->getRecipient();
                            $name = $user->firstname;
                            $notification->setMessage(
                                "Bienvenue sur " . CONF['Application']['Name'] . " $name. regardez les évènements près de vous et/ou créez en un ! Bonne découverte !"
                            );
                            return true;

                        /**
                         * Notification without category but with message
                         */
                        default:
                            if (!is_null($notification->getMessage()) && !empty($notification->getMessage())) return true;
                            else return false;

                    }

                /**
                 * Notification without type but with message
                 */
                default:
                    if (!is_null($notification->getMessage()) && !empty($notification->getMessage()))
                    {
                        $notification->icon = "notifications";
                        return true;
                    }
                    else
                    {
                        return false;
                    }

            }

        }

        /**
         * Get view of counter of notifications center
         * @return string
         */
        private function getViewCounterNotifications(): string
        {

            $connectedUser = $this->authenticationGateway->getConnectedUser();

            // LOAD ALL NOTIFICATIONS
            $loader = Notifications::manager($connectedUser);
            $loader->load();

            // RECOVER NUMBER OF NOTIFICATIONS
            $notificationsNumber = $loader->countUnread();

            // LOAD VIEW OF NOTIFICATIONS COUNTER
            if ($notificationsNumber > 0) return $this->render('notifications.view-counter', compact('notificationsNumber'));
            else return "";
        }

        /**
         * Run auto-reader of notifications center
         */
        public function autoReadNotification()
        {

            // CHECK IF UNREAD NOTIFICATIONS EXIST
            if (isset($_SESSION['UNREAD_NOTIFICATIONS']) && !empty($_SESSION['UNREAD_NOTIFICATIONS']))
            {

                foreach ($_SESSION['UNREAD_NOTIFICATIONS'] as $key => $notification)
                {

                    // READ
                    $notification->read();
                    // UNLOAD UNREAD NOTIFICATION
                    unset($_SESSION['UNREAD_NOTIFICATIONS'][$key]);

                }

            }

        }

        /**
         * Ajax router
         * @param string $action
         * @return null|string
         */
        public function ajaxSwitch(string $action)
        {

            switch ($action)
            {

                case 'update.notifications': // UPDATE NOTIFICATIONS
                    return $this->getViewNotifications();
                case 'update.counter': // UPDATE COUNTER OF NOTIFICATIONS
                    return $this->getViewCounterNotifications();
                case 'read':
                    $this->autoReadNotification(); // RUN NOTIFICATIONS AUTO-READER
                    break;

            }

            return null;
        }
    }
}