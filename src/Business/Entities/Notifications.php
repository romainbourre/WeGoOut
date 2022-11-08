<?php

namespace Business\Entities
{

    use Business\ValueObjects\FrenchDate;
    use System\Librairies\Database;


    /**
     * Class Notifications
     * Notifications System
     * @package App\Lib
     */
    class Notifications
    {

        /**
         * Event-type notification
         */
        public const TYPE_EVENT = 1;

        /**
         * user-type notification
         */
        public const TYPE_USER = 2;

        /**
         * publication-type notification
         */
        public const TYPE_PUBLICATION = 3;

        /**
         * review-type notification
         */
        public const TYPE_REVIEW = 4;

        /**
         * enjoy-type notification
         */
        public const TYPE_ENJOY = 5;

        /**
         * request-category in event notification
         */
        public const CATEGORY_EVENT_REQUEST = 1;

        /**
         * subscribe-category in event notification
         */
        public const CATEGORY_EVENT_SUBSCRIBE = 2;

        /**
         * unsubscribe-category in event notification
         */
        public const CATEGORY_EVENT_UNSUBSCRIBE = 3;

        /**
         * accept-category in event notification
         */
        public const CATEGORY_EVENT_ACCEPT = 4;

        /**
         * delete-category in event notification
         */
        public const CATEGORY_EVENT_DELETE = 5;

        /**
         * invitation send category in event notification
         */
        public const CATEGORY_EVENT_SEND_INVITATION = 6;

        /**
         * request-category in user notification
         */
        public const CATEGORY_USER_REQUEST = 1;

        /**
         * unrequest-category in user notification
         */
        public const CATEGORY_USER_UNREQUEST = 2;

        /**
         * accept-category in user notification
         */
        public const CATEGORY_USER_ACCEPT = 3;

        /**
         * delete-category in user notification
         */
        public const CATEGORY_USER_DELETE = 4;

        /**
         * publication-category in publication notification
         */
        public const CATEGORY_PUBLICATION_PUBLICATION = 1;

        /**
         * review-category in review notification
         */
        public const CATEGORY_REVIEW_REVIEW = 1;

        /**
         * welcome-category in enjoy notification
         */
        public const CATEGORY_ENJOY_WELCOME = 1;

        /**
         * @var int id of notification
         */
        private $id;

        /**
         * @var int recipient of notification
         */
        private $recipient;

        /**
         * @var int|null type of notification
         */
        private $type;

        /**
         * @var int|null category of notification
         */
        private $category;

        /**
         * @var int|null event target of notification
         */
        private $targetEvent;

        /**
         * @var int|null user target of notification
         */
        private $targetUser;

        /**
         * @var FrenchDate|null the datetime of dispatch of the notification
         */
        private $datetime_send;

        /**
         * @var FrenchDate|null the datetime of reading of the notification
         */
        private $datetime_read;

        /**
         * @var null|string message of the notification
         */
        private $message;

        /**
         * @var null|string action of the notification
         */
        private $action;

        /**
         * @var string icon
         */
        public string $icon;

        /**
         * Notifications constructor.
         * @param int $id
         * @param int $recipient
         * @param int $type
         * @param int $category
         * @param int $targetEvent
         * @param int $targetUser
         * @param FrenchDate|null $send
         * @param FrenchDate|null $read
         * @param string|null $message
         * @param string|null $action
         */
        public function __construct(int $id, int $recipient, ?int $type, ?int $category, ?int $targetEvent, ?int $targetUser, ?FrenchDate $send, ?FrenchDate $read, ?string $message, ?string $action)
        {
            $this->id = $id;
            $this->recipient = $recipient;
            $this->type = $type;
            $this->category = $category;
            $this->targetEvent = $targetEvent;
            $this->targetUser = $targetUser;
            $this->datetime_read = $read;
            $this->datetime_send = $send;
            $this->message = $message;
            $this->action = $action;
        }

        public static function manager(User $user): object
        {
            return new class($user)
            {

                /**
                 * @var User|null recipient of notification
                 */
                private $user = null;
                /**
                 * @var null iterable of notifications loaded
                 */
                private $notifications = null;

                public function __construct(User &$user)
                {
                    $this->user = $user;
                }

                /**
                 * Notifications add System
                 * @return object
                 */
                public function add()
                {
                    return new class($this->user)
                    {
                        private User $user;

                        public function __construct(User &$user)
                        {
                            $this->user = $user;
                        }

                        /**
                         * Leave a event-type notification
                         * @param string $category category of event-type notification
                         * @param Event|null $event event target
                         * @param User|null $user user target
                         */
                        public function event(string $category, Event $event = null, User $user = null): void
                        {
                            switch ($category)
                            {

                                case "request":
                                    $this->save(Notifications::TYPE_EVENT, Notifications::CATEGORY_EVENT_REQUEST, $event, $user);
                                    break;

                                case "unrequest":
                                    $bdd = Database::getDB();
                                    // CANCELED OPPOSITE NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_REQUEST);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    break;

                                case "subscribe":
                                    $bdd = Database::getDB();
                                    // CANCELED SAME NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_SUBSCRIBE);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // CANCELED OPPOSITE NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_UNSUBSCRIBE);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // CANCELED REQUEST
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_REQUEST);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // SEND NOTIFICATION
                                    $this->save(Notifications::TYPE_EVENT, Notifications::CATEGORY_EVENT_SUBSCRIBE, $event, $user);
                                    break;

                                case "unsubscribe":
                                    $bdd = Database::getDB();
                                    // CANCELED SAME NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_UNSUBSCRIBE);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // CANCELED OPPOSITE NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_SUBSCRIBE);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // SEND NOTIFICATION
                                    $this->save(Notifications::TYPE_EVENT, Notifications::CATEGORY_EVENT_UNSUBSCRIBE, $event, $user);
                                    break;

                                case "accept":
                                    $bdd = Database::getDB();
                                    // CANCELED SAME NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_ACCEPT);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // SEND NOTIFICATION
                                    $this->save(Notifications::TYPE_EVENT, Notifications::CATEGORY_EVENT_ACCEPT, $event, $user);
                                    break;

                                case "delete":
                                    $bdd = Database::getDB();
                                    // CANCELED SAME NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_ACCEPT);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    break;

                                case "send.invitation":
                                    $bdd = Database::getDB();
                                    // CANCELED SAME NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_SEND_INVITATION);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    // SEND NOTIFICATION
                                    $this->save(Notifications::TYPE_EVENT, Notifications::CATEGORY_EVENT_SEND_INVITATION, $event, $user);
                                    break;

                                case "delete.invitation":
                                    $bdd = Database::getDB();
                                    // CANCELED OPPOSITE NOTIFICATIONS
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT = :targetEvent AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_EVENT);
                                    $request->bindValue(':category', Notifications::CATEGORY_EVENT_SEND_INVITATION);
                                    $request->bindValue(':targetEvent', $event->getID());
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    break;

                                default:
                                    throw new class(__FUNCTION__) extends \Exception
                                    {
                                        public function __construct(string $function)
                                        {
                                            parent::__construct("NOTIFICATIONS : No category found in " . $function);
                                        }
                                    };
                                    break;
                            }
                        }

                        /**
                         * Leave a user-type notification
                         * @param string $category category of user notification
                         * @param User $user user target
                         */
                        public function user(string $category, User $user): void
                        {
                            switch ($category)
                            {

                                case "request":
                                    $null = null;
                                    $this->save(Notifications::TYPE_USER, Notifications::CATEGORY_USER_REQUEST, $null, $user);
                                    break;

                                case "unrequest":
                                    $bdd = Database::getDB();
                                    // CANCELED REQUEST
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT is null AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_USER);
                                    $request->bindValue(':category', Notifications::CATEGORY_USER_REQUEST);
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    break;

                                case "accept":
                                    $null = null;
                                    $this->save(Notifications::TYPE_USER, Notifications::CATEGORY_USER_ACCEPT, $null, $user);
                                    break;

                                case "delete":
                                    $bdd = Database::getDB();
                                    // CANCELED ACCEPT
                                    $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_VALID = 0 WHERE USER_ID = :recipientId AND NOTIF_TYPE = :type AND NOTIF_CATEGORY = :category AND NOTIF_TARGET_EVENT is null AND NOTIF_TARGET_USER = :targetUser AND NOTIF_DATETIME_SEND is not null AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1');
                                    $request->bindValue(':recipientId', $this->user->getID());
                                    $request->bindValue(':type', Notifications::TYPE_USER);
                                    $request->bindValue(':category', Notifications::CATEGORY_USER_ACCEPT);
                                    $request->bindValue(':targetUser', $user->getID());
                                    $request->execute();
                                    break;

                                default:
                                    throw new class(__FUNCTION__) extends \Exception
                                    {
                                        public function __construct(string $function)
                                        {
                                            parent::__construct("NOTIFICATIONS : No category found in " . $function);
                                        }
                                    };
                                    break;

                            }
                        }

                        /**
                         * Leave a publication-type notification
                         * @param string $category category of publication-type
                         * @param Event $event event target
                         * @param User $user user target
                         */
                        public function publication(string $category, Event $event, User $user)
                        {
                            switch ($category)
                            {

                                case "publication":
                                    $this->save(Notifications::TYPE_PUBLICATION, Notifications::CATEGORY_PUBLICATION_PUBLICATION, $event, $user);
                                    break;

                                default:
                                    throw new class(__FUNCTION__) extends \Exception
                                    {
                                        public function __construct(string $function)
                                        {
                                            parent::__construct("NOTIFICATIONS : No category found in " . $function);
                                        }
                                    };
                                    break;

                            }
                        }

                        /**
                         * Leave a review-type notification
                         * @param string $category category of review-type
                         * @param Event $event event target
                         * @param User $user user target
                         */
                        public function review(string $category, Event $event, User $user)
                        {
                            switch ($category)
                            {

                                case "review":
                                    $this->save(Notifications::TYPE_REVIEW, Notifications::CATEGORY_REVIEW_REVIEW, $event, $user);
                                    break;

                                default:
                                    throw new class(__FUNCTION__) extends \Exception
                                    {
                                        public function __construct(string $function)
                                        {
                                            parent::__construct("NOTIFICATIONS : No category found in " . $function);
                                        }
                                    };
                                    break;

                            }
                        }

                        /**
                         * Leave a enjoy-type notification
                         * @param string $category category of enjoy-type
                         * @param Event|null $event event target
                         * @param User|null $user user target
                         */
                        public function enjoy(string $category, Event $event = null, User $user = null)
                        {
                            switch ($category)
                            {

                                case "welcome":
                                    $this->save(Notifications::TYPE_ENJOY, Notifications::CATEGORY_ENJOY_WELCOME, $event, $user);
                                    break;

                                default:
                                    throw new class(__FUNCTION__) extends \Exception
                                    {
                                        public function __construct(string $function)
                                        {
                                            parent::__construct("NOTIFICATIONS : No category found in " . $function);
                                        }
                                    };
                                    break;

                            }
                        }

                        /**
                         * Save a new notification in database
                         * @param int $type type of notification
                         * @param int $category category of notification
                         * @param Event|null $targetEvent event target of notification
                         * @param User|null $targetUser user target of notification
                         * @return bool
                         */
                        private function save(int $type, int $category, ?Event &$targetEvent, ?User &$targetUser)
                        {

                            $bdd = Database::getDB();

                            if (is_null($targetEvent)) $event = null;
                            else $event = $targetEvent->getID();
                            if (is_null($targetUser)) $user = null;
                            else $user = $targetUser->getID();

                            $request = $bdd->prepare('INSERT INTO NOTIFICATIONS(USER_ID, NOTIF_TYPE, NOTIF_CATEGORY, NOTIF_TARGET_EVENT, NOTIF_TARGET_USER, NOTIF_DATETIME_SEND) VALUES (:recipientId, :type, :category, :targetEvent, :targetUser, sysdate())');
                            $request->bindValue(':recipientId', $this->user->getID());
                            $request->bindValue(':type', $type);
                            $request->bindValue(':category', $category);
                            $request->bindValue(':targetEvent', $event);
                            $request->bindValue(':targetUser', $user);

                            return $request->execute();

                        }

                    };
                }

                /**
                 * Load notifications of user
                 * @param int|null $limit limit number of notification
                 * @return iterable|null list of notifications
                 */
                public function load(int $limit = null): ?iterable
                {

                    $bdd = Database::getDB();

                    if (!is_null($limit)) $requestLimit = "LIMIT " . $limit;
                    else $requestLimit = "";
                    $request = $bdd->prepare('SELECT * FROM NOTIFICATIONS WHERE USER_ID = :userId AND NOTIF_VALID = 1 ORDER BY NOTIF_DATETIME_SEND DESC ' . $requestLimit);
                    $request->bindValue(':userId', $this->user->getID());
                    if ($request->execute())
                    {
                        $this->notifications = array();
                        $this->notRead = 0;
                        while ($result = $request->fetch())
                        {
                            $id = (int)$result['NOTIF_ID'];
                            $recipient = (int)$result['USER_ID'];
                            $type = $result['NOTIF_TYPE'];
                            $category = $result['NOTIF_CATEGORY'];
                            $targetEvent = $result['NOTIF_TARGET_EVENT'];
                            $targetUser = $result['NOTIF_TARGET_USER'];
                            if (!is_null($result['NOTIF_DATETIME_SEND'])) $send = new FrenchDate(strtotime($result['NOTIF_DATETIME_SEND']));
                            else $send = null;
                            if (!is_null($result['NOTIF_DATETIME_READ']))
                            {
                                $read = new FrenchDate(strtotime($result['NOTIF_DATETIME_READ']));
                            }
                            else
                            {
                                $read = null;
                            }
                            $message = (string)$result['NOTIF_MESSAGE'];
                            $action = (string)$result['NOTIF_ACTION'];

                            $this->notifications[] = new Notifications($id, $recipient, $type, $category, $targetEvent, $targetUser, $send, $read, $message, $action);

                        }
                        return $this->notifications;
                    }
                    return null;

                }

                /**
                 * Get list of notifications
                 * @return iterable|null
                 * @throws \Exception
                 */
                public function getNotifications(): ?iterable
                {

                    if (!is_null($this->notifications))
                    {
                        return $this->notifications;
                    }
                    else
                    {
                        throw new class extends \Exception
                        {
                            public function __construct()
                            {
                                parent::__construct("Load notifications before use this function");
                            }
                        };
                    }

                }

                /**
                 * Get number of notifications loaded
                 * @return int
                 * @throws \Exception
                 */
                public function count(): int
                {

                    if (!is_null($this->notifications))
                    {
                        return count($this->notifications);
                    }
                    else
                    {
                        throw new class extends \Exception
                        {
                            public function __construct()
                            {
                                parent::__construct("Load notifications before use this function");
                            }
                        };
                    }

                }

                /**
                 * Count number of notifications for an user
                 * @return int|null number of notifications
                 */
                public function countUnread(): ?int
                {

                    $bdd = Database::getDB();

                    $request = $bdd->prepare('SELECT count(*) as nbrNotifications FROM NOTIFICATIONS WHERE USER_ID = :userId AND NOTIF_DATETIME_READ is null AND NOTIF_VALID = 1 ORDER BY NOTIF_DATETIME_SEND DESC');
                    $request->bindValue(':userId', $this->user->getID());
                    if ($request->execute())
                    {
                        return (int)$request->fetch()['nbrNotifications'];
                    }

                    return null;

                }

            };
        }

        /**
         * Read notifications
         * @return bool
         */
        public function read(): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('UPDATE NOTIFICATIONS SET NOTIF_DATETIME_READ = sysdate() WHERE NOTIF_ID = :id');
            $request->bindValue(':id', $this->id);

            return $request->execute();

        }

        /**
         * Load view of notification
         * @return string
         */
        public function getView(): string
        {
            switch ($this->category)
            {
                default:
                    $logo = "notifications";
                    break;
            }
            if (!empty($this->action)) $link = 'href="' . $this->action . '"';
            else $link = "";
            if ($this->isUnread()) $background = "blue lighten-5";
            else $background = "";
            $content = '<li class="' . $background . '"><a ' . $link . '><span>' . $this->message . '<br><small>' . $this->datetime_send->getRelativeDateAndHours() . '</small></span><i class="material-icons right">' . $logo . '</i></a></li><li class="divider"></li>';
            return $content;
        }

        /**
         * Check if notification is unread
         * @return bool
         */
        public function isUnread(): bool
        {
            return is_null($this->datetime_read);
        }

        /**
         * Get id of notification
         * @return int
         */
        public function getId(): int
        {
            return $this->id;
        }

        /**
         * Get recipient of notification
         * @return User
         */
        public function getRecipient(): User
        {
            return User::load($this->recipient);
        }

        /**
         * Get type of notification
         * @return int
         */
        public function getType(): ?int
        {
            return $this->type;
        }

        /**
         * Get category of notification
         * @return int
         */
        public function getCategory(): ?int
        {
            return $this->category;
        }

        /**
         * Get event linked at notification
         * @return Event
         */
        public function getTargetEvent(): ?Event
        {
            if (!is_null($this->targetEvent)) return new Event($this->targetEvent);
            return null;
        }

        /**
         * Get user linked at notification
         * @return User
         */
        public function getTargetUser(): ?User
        {
            if (!is_null($this->targetUser)) return User::load($this->targetUser);
            return null;
        }

        /**
         * Get send datetime of notification
         * @return FrenchDate|null
         */
        public function getDatetimeSend(): ?FrenchDate
        {
            return $this->datetime_send;
        }

        /**
         * Get read datetime of notification
         * @return FrenchDate|null
         */
        public function getDatetimeRead(): ?FrenchDate
        {
            return $this->datetime_read;
        }

        /**
         * Get action of notification
         * @return mixed
         */
        public function getAction(): ?string
        {
            return $this->action;
        }

        /**
         * Get action of notification
         * @return mixed
         */
        public function getMessage(): ?string
        {
            return $this->message;
        }

        /**
         * Set message in notification
         * @param string $message
         */
        public function setMessage(string $message)
        {
            $this->message = $message;
        }

        /**
         * Set action in notification
         * @param string $action
         */
        public function setAction(string $action)
        {
            $this->action = $action;
        }
    }
}

