<?php

namespace Domain\Entities
{

    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\UserNotExistException;
    use System\Librairies\Database;

    /**
     * Represent an event
     * Class Event
     * @package App\Lib
     * @author Bourré Romain
     */
    class Event
    {

        private static $numberEvent = 0;

        private $id;
        private $user;
        private $title;
        private $description;
        private $catId;
        private $datetimeBegin;
        private $datetimeEnd;
        private $datetimeCreate;
        private $address;
        private $postalCode;
        private $city;
        private $addressComplements;
        private $maxOfPart;
        private $placeID;
        private $location;
        private $price;
        private $status;
        private $guestOnly;
        private $datetimeCanceled;
        private $datetimeDelete;
        private $eventValid;

        /**
         * Event constructor.
         * @param int $id id of event
         * @throws EventNotExistException
         */
        public function __construct(int $id)
        {
            if (!is_null($id) && is_int($id))
            {
                $this->load($id);
                self::$numberEvent++;
            }
        }

        /**
         * Loading data of event from database
         * @param int $id id of the event
         * @return boolean
         * @throws EventNotExistException
         */
        private function load(int $id): bool
        {

            if (!is_null($id))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('SELECT * FROM EVENT WHERE event_id = :id AND EVENT_DATETIME_DELETE is null');
                $request->bindValue(':id', $id);
                if ($request->execute())
                {

                    $result = $request->fetch();
                    if (!empty($result))
                    {
                        $this->id = $result['EVENT_ID'];
                        $this->user = User::loadUserById((int)$result['USER_ID']);
                        $this->title = (string)$result['EVENT_TITLE'];
                        $this->description = (string)$result['EVENT_DESCRIPTION'];
                        $this->catId = $result['CAT_ID'];
                        $this->datetimeCreate = strtotime($result['EVENT_DATETIME_CREATE']);
                        $this->datetimeBegin = strtotime($result['EVENT_DATETIME_BEGIN']);
                        if (!is_null($t = $result['EVENT_DATETIME_END'])) $this->datetimeEnd = strtotime($t);
                        else $this->datetimeEnd = null;
                        $this->address = $result['EVENT_LOCATION_ADDRESS'];
                        $this->addressComplements = (string)$result['EVENT_LOCATION_COMPLEMENTS'];
                        $this->postalCode = $result['EVENT_LOCATION_CP'];
                        $this->city = $result['EVENT_LOCATION_CITY'];
                        $this->maxOfPart = $result['EVENT_PARTICIPANTS_NUMBER'];
                        $this->placeID = $result['EVENT_LOCATION_PLACE_ID'];
                        $this->location = new Location((double)$result['EVENT_LOCATION_LAT'], (double)$result['EVENT_LOCATION_LNG']);
                        $this->location->setAddress($this->address);
                        $this->location->setPostalCode($this->postalCode);
                        $this->location->setCity($this->city);
                        $this->location->setComplements((string)$result['EVENT_LOCATION_COMPLEMENTS']);
                        $this->location->setGooglePlaceId($result['EVENT_LOCATION_PLACE_ID']);
                        $this->price = $result['EVENT_PRICE'];
                        $this->status = $result['EVENT_CIRCLE'];
                        $this->guestOnly = $result['EVENT_GUEST_ONLY'];
                        $this->datetimeCanceled = $result['EVENT_DATETIME_CANCEL'];
                        $this->datetimeDelete = $result['EVENT_DATETIME_DELETE'];
                        $this->eventValid = (int)$result['EVENT_VALID'];


                        return true;

                    }
                    else
                    {
                        throw new EventNotExistException();
                    }

                }

            }

            return false;

        }

        /**
         * @return int number of event instance
         */
        public static function getNumberEvent(): int
        {
            return self::$numberEvent;
        }

        /**
         * Reload data of event
         * @return boolean
         * @throws EventCanceledException
         * @throws EventDeletedException
         * @throws EventNotExistException
         * @throws EventSignaledException
         */
        public function update(): bool
        {
            if (!is_null($this->id)) return $this->load($this->id);
        }

        /**
         * Get id of event
         * @return mixed id of event
         */
        public function getID(): int
        {
            return $this->id;
        }

        /**
         * Get user of event
         * @return User instance of User
         */
        public function getUser(): User
        {
            return $this->user;
        }

        /**
         * Get title of event
         * @return string title of event
         */
        public function getTitle(): string
        {
            return $this->title;
        }

        /**
         * Get description of event
         * @return string description of event
         */
        public function getDescription(): string
        {
            return $this->description;
        }

        /**
         * Get name of the event category
         * @return mixed name of category
         */
        public function getCategory()
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare('SELECT * FROM CATEGORY WHERE cat_id = :catId');
            $request->bindValue(':catId', $this->catId);
            if ($request->execute())
            {
                return $request->fetch();
            }
        }

        /**
         * Datetime of creation of the event
         * @return int timestamp
         */
        public function getDatetimeCreate(): int
        {
            return $this->datetimeCreate;
        }

        /**
         * Datetime of start of the event
         * @return int timestamp
         */
        public function getDatetimeBegin(): int
        {
            return $this->datetimeBegin;
        }

        /**
         * Datetime of end of the event
         * @return int timestamp
         */
        public function getDatetimeEnd(): ?int
        {
            return $this->datetimeEnd;
        }

        /**
         * Address of the event
         * @return string address
         */
        public function getAddress(): string
        {
            return $this->address;
        }

        /**
         * Address complements of the event
         * @return string address complements
         */
        public function getAddressComplements(): string
        {
            return $this->addressComplements;
        }

        /**
         * Postal code of the event
         * @return string postal code
         */
        public function getPostalCode(): string
        {
            return $this->postalCode;
        }

        /**
         * City of the event
         * @return string city
         */
        public function getCity(): string
        {
            return $this->city;
        }

        /**
         * Get number of maximum participants  of the event
         * @return int maximum participants
         */
        public function getMaxPart(): ?int
        {
            return $this->maxOfPart;
        }

        /**
         * Get Google place id of location of the event
         * @return string Google place ID
         */
        public function getPlaceID(): string
        {
            return $this->placeID;
        }

        /**
         * Get location of the event
         * @return Location location
         */
        public function getLocation(): Location
        {
            return $this->location;
        }

        /**
         * Get distance between this event and other event
         * @param Event $event other event
         * @return float distance
         */
        public function getDistance(Event $event): float
        {
            return ($this->location)->getDistance($event->getLocation());
        }

        /**
         * Get publication status of the event
         * @return int status
         */
        public function getStatus(): int
        {
            return $this->status;
        }

        /**
         * Check if the event is public
         * @return bool
         */
        public function isPublic(): bool
        {
            return ($this->status == 1);
        }

        /**
         * Check if the event is private
         * @return bool
         */
        public function isPrivate(): bool
        {
            return ($this->status == 2);
        }

        /**
         * Check if the event is for guest only
         * @return bool
         */
        public function isGuestOnly(): bool
        {
            if ($this->guestOnly == 1) return true;
            return false;
        }

        /**
         * Compare the event with one other
         * @param Event $event
         * @return bool
         */
        public function equals(Event $event): bool
        {
            return ($this->id == $event->getID());
        }

        /**
         * Get number of user every category
         * @return int
         */
        public function getNumbAll(): int
        {

            $bdd = Database::getDB();

            if ($this->managersConfidentiality()) $WAIT = '';
            else $WAIT = 'AND PART_DATETIME_ACCEPT is not null';
            $SQL = 'SELECT count(user_id) as nbrPart FROM PARTICIPATE WHERE event_id = :eventId ' . $WAIT . ' AND PART_DATETIME_DELETE is null UNION ALL SELECT count(distinct user_id) as nbrPart FROM GUEST LEFT JOIN PARTICIPATE USING(user_id, event_id) WHERE GUEST.event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null AND ( (PART_DATETIME_SEND is null AND PART_DATETIME_ACCEPT is null) OR (PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is null)) UNION ALL SELECT count(GUEST_EMAIL) FROM GUEST_TEMP_EMAIL WHERE GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null';
            $SUM = 'SELECT SUM(nbrPart) as nbrPart FROM (' . $SQL . ') tab';

            $request = $bdd->prepare($SUM);
            $request->bindValue(':eventId', $this->id);
            if ($test = $request->execute())
            {
                $result = $request->fetch();
                if (!is_null($result['nbrPart'])) return $result['nbrPart'];
            }

            return 0;

        }

        /**
         * Get actual number of participants
         * @return int number of participant
         */
        public function getNumbParticipants(): int
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(user_id) as nbrPart FROM PARTICIPATE WHERE event_id = :id AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null');
            $request->bindValue(':id', $this->id);
            if ($request->execute())
            {
                $result = $request->fetch();
                if (!is_null($result['nbrPart'])) return $result['nbrPart'];
            }

            return 0;

        }

        /**
         * Get number of pending participants
         * @return int number of pending participants
         */
        public function getNumbParticipantsWait(): int
        {

            if ($this->managersConfidentiality())
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('SELECT count(user_id) as nbrPart FROM PARTICIPATE WHERE event_id = :id AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is null AND PART_DATETIME_DELETE is null');
                $request->bindValue(':id', $this->id);
                if ($request->execute())
                {
                    $result = $request->fetch();
                    if (!is_null($result['nbrPart'])) return $result['nbrPart'];
                }

            }

            return 0;

        }

        /**
         * Get number of invited participants
         * @return int number of invited participants
         */
        public function getNumbInvited(): int
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT sum(nbrInv) as nbrInv FROM ( SELECT count(user_id) as nbrInv FROM GUEST WHERE event_id = :id AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null UNION SELECT count(GUEST_EMAIL) as nbrInv FROM GUEST_TEMP_EMAIL WHERE event_id = :id AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null ) tab');
            $request->bindValue(':id', $this->id);
            if ($request->execute())
            {
                $result = $request->fetch();
                if (!is_null($result['nbrInv'])) return $result['nbrInv'];
            }

            return 0;

        }

        /**
         * Get price of event if exist
         * @return float|null price
         */
        public function getPrice(): ?float
        {
            if (!is_null($this->price)) return (float)$this->price;
            return null;
        }

        /**
         * Get id and name of category
         * @return array array of category
         */
        public static function getAllCategory(): array
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT * FROM CATEGORY');
            if ($request->execute())
            {
                return $request->fetchAll();
            }

            return [];
        }

        /**
         * Check if the event started
         * @return bool
         */
        public function isStarted(): bool
        {
            if (is_null($this->datetimeEnd))
            {
                return ($this->datetimeBegin < time());
            }
            else
            {
                return ($this->datetimeBegin < time() && time() < $this->datetimeEnd);
            }
        }

        /**
         * Check if the event it's over
         * @return bool
         */
        public function isOver(): bool
        {
            if (is_null($this->datetimeEnd))
            {
                return ($this->datetimeBegin <= time() && date("d/m/Y") != date("d/m/Y", $this->datetimeBegin));
            }
            else
            {
                return ($this->datetimeEnd < time());
            }
        }

        /**
         * Check if an user is creator of the event
         * @param User $user user
         * @return bool
         */
        public function isCreator(User $user): bool
        {
            return ($this->user->getID() == $user->getID());
        }

        /**
         * Check if an user is an organizer of the event
         * @param User $user user
         * @return bool|null return null if request break
         */
        public function isOrganizer(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as exist FROM ORGANIZE WHERE event_id = :eventId AND user_id = :userId AND ORGANIZE.ORG_DATETIME_SET is not null AND ORG_DATETIME_UNSET is null');
            $request->bindValue(':eventId', $this->id);
            $request->bindValue(':userId', $user->getID());

            if ($request->execute())
            {

                $result = $request->fetch();

                if ($result['exist'] > 0) return true;

                return false;

            }

            return null;

        }

        /**
         * Check if an user is invited to the event
         * @param User $user user
         * @return bool
         */
        public function isInvited(User $user): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) isInvited FROM GUEST WHERE user_id = :userId AND event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null');
            $request->bindValue(':userId', $user->getID());
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {
                $result = $request->fetch();
                if ($result['isInvited'] > 0) return true;
            }

            return false;

        }

        /**
         * Check if email address is invited to the event
         * @param string $email user
         * @return bool
         */
        public function isEmailInvited(string $email): bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) isInvited FROM GUEST_TEMP_EMAIL WHERE GUEST_EMAIL = :email AND event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null');
            $request->bindValue(':email', $email);
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {
                $result = $request->fetch();
                if ($result['isInvited'] > 0) return true;
            }

            return false;

        }

        /**
         * Check if an user participate to the event
         * @param User $user user
         * @return bool|null return null if request break
         */
        public function isParticipant(User $user): ?bool
        {
            return ($this->isParticipantWait($user) || $this->isParticipantValid($user));
        }

        /**
         * Check if an user is pending participant
         * @param User $user
         * @return bool|null return null if request break
         */
        public function isParticipantWait(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT COUNT(*) as exist FROM PARTICIPATE WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is null AND PART_DATETIME_DELETE is null');
            $request->bindValue(':eventId', $this->id);
            $request->bindValue(':userId', $user->getID());

            if ($request->execute())
            {
                $result = $request->fetch();
                if ($result['exist'] > 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }

            return null;

        }

        /**
         * Check if user is valid participant
         * @param User $user
         * @return bool|null return null if request break
         */
        public function isParticipantValid(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT COUNT(*) as exist FROM PARTICIPATE WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null');
            $request->bindValue(':eventId', $this->id);
            $request->bindValue(':userId', $user->getID());

            if ($request->execute())
            {
                $result = $request->fetch();
                if ($result['exist'] > 0)
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }

            return null;

        }

        /**
         * Send registration request at the event for an user, if the user is invited then valid participation
         * @param User $user user
         * @return bool
         */
        public function sendRegistrationAsk(User $user): bool
        {

            if (!$this->isStarted() || !$this->isParticipantWait($user) || !$this->isParticipantValid($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('INSERT INTO PARTICIPATE(event_id, user_id, part_datetime_send) VALUES(:eventId, :userId, sysdate())');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':userId', $user->getID());

                return $request->execute();

            }

            return false;

        }

        /**
         * Set an user as valid participant (if registration request send before
         * @param User $user user
         * @return bool
         */
        public function setParticipantAsValid(User $user): bool
        {

            if (!$this->isStarted() && ($this->managersConfidentiality() || ($user->getID() == $_SESSION['USER_DATA']->getID() && $this->isInvited($_SESSION['USER_DATA']))))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE PARTICIPATE SET part_datetime_accept = sysdate() WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is null AND  PART_DATETIME_DELETE is null ');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':userId', $user->getID());

                return $request->execute();


            }

            return false;

        }

        /**
         * Check the confidentiality for managers
         * @return bool
         */
        private function managersConfidentiality(): bool
        {
            return ($this->isCreator($_SESSION['USER_DATA']) || $this->isOrganizer($_SESSION['USER_DATA']));
        }


        /**
         * Unset participant user of the event
         * @param User $user user
         * @return bool
         */
        public function unsetParticipant(User $user): bool
        {
            if ($this->managersConfidentiality()) return ($this->unsetRegistration($user) && $this->unsetInvitation($user));
            return false;
        }

        /**
         * @param User|null $user
         * @param null|string $email
         * @return bool
         */
        public function sendInvitation(?User $user, ?string $email = null): bool
        {

            $bdd = Database::getDB();

            if (!is_null($user))
            {

                $request = $bdd->prepare('INSERT INTO GUEST(EVENT_ID, USER_ID, GUEST_DATETIME_SEND) VALUES (:eventId, :userId, sysdate())');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue('userId', $user->getID());

                return $request->execute();

            }
            else if (is_null($user) && !is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            {

                $request = $bdd->prepare('INSERT INTO GUEST_TEMP_EMAIL(EVENT_ID, GUEST_EMAIL, GUEST_DATETIME_SEND) VALUES (:eventId, :userId, sysdate())');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue('userId', $email);

                return $request->execute();

            }

            return false;

        }

        /**
         * Unset invitation for an user of the event
         * @param User|null $user user
         * @param string|null $email
         * @return bool
         */
        public function unsetInvitation(?User $user, ?string $email = null): bool
        {


            if (!is_null($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE GUEST SET GUEST_DATETIME_DELETE = sysdate() WHERE event_id = :eventId AND user_id = :userId AND GUEST_DATETIME_DELETE IS NULL ');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':userId', $user->getID());

                return $request->execute();

            }
            else if (is_null($user) && !is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE GUEST_TEMP_EMAIL SET GUEST_DATETIME_DELETE = sysdate() WHERE event_id = :eventId AND GUEST_EMAIL = :email AND GUEST_DATETIME_DELETE IS NULL ');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':email', $email);

                return $request->execute();

            }

            return false;

        }

        /**
         * Unset registration request for an user
         * @param User $user user
         * @return bool
         */
        public function unsetRegistration(User $user): bool
        {

            if ($this->managersConfidentiality() || $user->equals($_SESSION['USER_DATA']))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE PARTICIPATE SET part_datetime_delete = sysdate() WHERE event_id = :eventId AND user_id = :userId AND PART_DATETIME_SEND is not null AND part_datetime_delete is null ');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':userId', $user->getID());

                return $request->execute();

            }

            return false;

        }

        /**
         * Get organizers of event
         * @return iterable|null organizers list
         * @throws UserNotExistException
         */
        public function getOrganizers(): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT USER_ID FROM EVENT WHERE EVENT_ID = :eventId UNION SELECT USER_ID FROM ORGANIZE WHERE EVENT_ID = :eventId AND ORG_DATETIME_SET is not null AND ORG_DATETIME_UNSET is null');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $organizers = array();

                while ($result = $request->fetch())
                {

                    $organizers[] = User::loadUserById((int)$result['USER_ID']);

                }

                return $organizers;

            }

            return null;

        }

        /**
         * Get array of users who are participants or/and invited or/and bending participants
         * @param int $level 0 = All | 1 = Valid participants | 2 = Bending participants | 3 = Invited
         * @return iterable|null array of user
         * @throws UserNotExistException
         */
        public function getParticipants(int $level = 0): ?iterable
        {

            $bdd = Database::getDB();

            switch ($level)
            {
                case 0: // ALL
                    if ($this->managersConfidentiality()) $WAIT = '';
                    else $WAIT = 'AND PART_DATETIME_ACCEPT is not null';
                    $SQL = 'SELECT user_id FROM PARTICIPATE WHERE event_id = :eventId AND PART_DATETIME_SEND is not null ' . $WAIT . ' AND PART_DATETIME_DELETE is null UNION SELECT user_id FROM GUEST LEFT JOIN PARTICIPATE USING(user_id, event_id) WHERE GUEST.event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null';
                    break;
                case 1: // VALID PARTICIPANT ONLY
                    $SQL = 'SELECT user_id FROM PARTICIPATE WHERE event_id = :eventId AND PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null';
                    break;
                case 2: // PARTICIPANT IN WAITING ONLY
                    if ($this->managersConfidentiality()) $SQL = 'SELECT user_id FROM PARTICIPATE WHERE event_id = :eventId AND PART_DATETIME_SEND is not null AND PART_DATETIME_ACCEPT is null AND PART_DATETIME_DELETE is null';
                    else $SQL = '';
                    break;
                case 3: // INVITED ONLY
                    $SQL = 'SELECT user_id FROM GUEST WHERE GUEST.event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null';
                    break;
            }

            $request = $bdd->prepare('SELECT tab.user_id FROM (' . $SQL . ') tab LEFT JOIN USER USING(user_id) LEFT JOIN META_USER_CLI USING(user_id) LEFT JOIN META_USER_PRO USING(user_id)  WHERE USER.USER_DATETIME_DELETE is null AND USER.USER_VALID = 1 ORDER BY META_USER_CLI.CLI_LASTNAME, META_USER_CLI.CLI_FIRSTNAME, META_USER_PRO.PRO_NAME ASC');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $user = array();

                while ($result = $request->fetch())
                {
                    $user[] = User::loadUserById($result['user_id']);
                }

                return $user;

            }

            return null;

        }

        /**
         * Get invitation to e-mail address
         * @return iterable|null
         */
        public function getEmailInvitation(): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT GUEST_EMAIL FROM GUEST_TEMP_EMAIL WHERE event_id = :eventId AND GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null ORDER BY GUEST_EMAIL ASC');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $user = array();

                while ($result = $request->fetch())
                {
                    $user[] = $result['GUEST_EMAIL'];
                }

                return $user;

            }

            return null;

        }

        /**
         * Get the number of reviews for the event
         * @return int number of reviews
         */
        public function getNumbReviews(): int
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as nbrRev FROM REVIEW WHERE event_id = :eventId AND rev_datetime_delete is null AND rev_valid = 1');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $result = $request->fetch();
                return (int)$result['nbrRev'];

            }

            return 0;

        }

        /**
         * Get reviews of the event from users
         * @return iterable|null reviews of users
         */
        public function getReviews(): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT rev_id FROM REVIEW WHERE event_id = :eventId AND REV_DATETIME_LEAVE is not null AND rev_datetime_delete is null AND rev_valid = 1 ORDER BY rev_datetime_leave DESC');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $reviews = array();

                while ($result = $request->fetch())
                {
                    $reviews[] = new Review($result['rev_id']);
                }

                return $reviews;

            }

            return null;

        }

        /**
         * Get a average rating of the reviews of the event
         * @return float|null average rating
         */
        public function getAverageRating(): ?float
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT AVG(rev_note) as average FROM REVIEW WHERE event_id = :eventId AND REV_DATETIME_LEAVE is not null AND rev_datetime_delete is null AND rev_valid = 1');
            $request->bindValue(':eventId', $this->id);

            if ($request->execute())
            {

                $result = $request->fetch();

                return $result['average'];

            }

            return null;

        }

        /**
         * Set a new review for the event
         * @param int $revNote note of review
         * @param string|null $revText text of review
         * @return bool
         */
        public function setNewReview(int $revNote, string $revText = null): bool
        {

            $me = $_SESSION['USER_DATA'];

            if (!Review::checkUserPostReview($me, new Event($this->id)))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('INSERT INTO REVIEW(event_id, user_id, rev_note, rev_text, rev_datetime_leave) VALUES(:eventId, :userId, :revNote, :revText, sysdate() )');
                $request->bindValue(':eventId', $this->id);
                $request->bindValue(':userId', $me->getID());
                $request->bindValue(':revNote', $revNote);
                $request->bindValue(':revText', $revText);

                return $request->execute();

            }
            else
            {

                return false;

            }

        }

        /**
         * Add task in event
         * @param string $label
         * @return bool
         */
        public function addTask(string $label): bool
        {
            return Task::addTask($this, $label);
        }
    }
}