<?php

namespace Domain\Entities
{

    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserIncorrectPasswordException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Exceptions\UserNotExistException;
    use System\Librairies\Database;

    /**
     * Class User
     * Represent a user
     * @package App\Lib
     * @author Romain Bourré
     */
    abstract class User
    {

        private const DEFAULT_PICTURE = "/assets/img/33aeda9.png";

        private $id;
        private $email;
        private $userDatetimeRegistration;
        private $picture;
        private $birthDate;
        private $location;
        private $userDatetimeDelete;
        private $userValid;
        private $userType;


        /**
         * Load generals data of user from database
         * @param int $id
         * @throws UserNotExistException
         */
        protected function load(int $id): void
        {
            // SAVE ID
            $this->id = $id;
            // RECOVER DATABASE
            $bdd = Database::getDB();
            // COMPOSE DATABASE REQUEST
            $request = $bdd->prepare('SELECT * FROM USER WHERE user_id = :id AND USER_DATETIME_DELETE is null');
            $request->bindValue(':id', $id);
            // EXECUTE DATABASE REQUEST
            if ($request->execute()) {
                $result = $request->fetch();
                if (!empty($result)) {
                    // SAVE GENERALS DATA OF USER
                    $this->email = $result['USER_EMAIL'];
                    $this->userDatetimeRegistration = new Date(strtotime($result['USER_DATETIME_REGISTRATION']));
                    $this->picture = $result['USER_PROFILE_PICTURE'];
                    $this->birthDate = new Date(strtotime((string)$result['USER_DATE_BIRTH']));
                    $this->location = new Location(
                        (double)$result['USER_LOCATION_LAT'],
                        (double)$result['USER_LOCATION_LNG']
                    );
                    $this->location->setLabel((string)$result['USER_LOCATION_LABEL']);
                    $this->location->setAddress((string)$result['USER_LOCATION_ADDRESS']);
                    $this->location->setPostalCode((string)$result['USER_LOCATION_CP']);
                    $this->location->setCity((string)$result['USER_LOCATION_CITY']);
                    $this->location->setCountry((string)$result['USER_LOCATION_COUNTRY']);
                    $this->location->setGooglePlaceId((string)$result['USER_LOCATION_PLACE_ID']);
                    $this->userDatetimeDelete = $result['USER_DATETIME_DELETE'];
                    $this->userValid = (int)$result['USER_VALID'];
                    $this->userType = (int)$result['USER_TYPE'];
                } else {
                    throw new UserNotExistException("L'utilisateur n'existe pas"); // Throw exception if user not exist
                }
            }
        }

        /**
         * Get ID of user
         * @return int ID
         */
        public function getID(): int
        {
            return $this->id;
        }

        /**
         * Get name of client user or professional user
         * @param string|null $type full : last name and first name
         * @return null|string
         */
        public function getName(string $type = null): ?string
        {
            $type = strtolower($type);
            return match ($type) {
                "full" => $this->getFirstname() . " " . $this->getLastname(),
                default => $this->getFirstname(),
            };
        }

        /**
         * Get E-mail of user
         * @return string e-mail
         */
        public function getEmail(): string
        {
            return $this->email;
        }

        /**
         * Get path of profile's picture user
         * @return string
         */
        public function getPicture(): string
        {
            if (is_null($this->picture)) {
                return self::DEFAULT_PICTURE;
            }
            return $this->picture;
        }

        /**
         * Get location of the user
         * @return Location location of user
         */
        public function getLocation(): Location
        {
            return $this->location;
        }

        /**
         * Get birth day of the user
         * @return Date Date object
         */
        public function getBirthDate(): Date
        {
            return $this->birthDate;
        }

        /**
         * Load user from email and password
         * @param string $email e-mail
         * @param string|null $pwd password
         * @return User|null
         * @throws UserIncorrectPasswordException
         * @throws UserNotExistException
         */
        public static function loadUserByEmail(string $email, string $pwd = null): ?User
        {
            // RECOVER DATABASE
            $bdd = Database::getDB();
            // PREPARE DATABASE REQUEST
            $request = $bdd->prepare('SELECT USER_ID, USER_PASSWORD, USER_TYPE FROM USER WHERE USER_EMAIL = :id');
            $request->bindValue(':id', $email);
            // EXECUTE REQUEST
            if ($request->execute()) {
                if ($result = $request->fetch()) {
                    $userId = (int)$result['USER_ID'];
                    $userType = (int)$result['USER_TYPE'];
                    // CHECK PASSWORD
                    if (!is_null($pwd) && (string)$result['USER_PASSWORD'] == $pwd) {
                        // FIND CLASS OF USER
                        switch ($userType) {
                            case 0:
                                return new UserCli($userId);
                                break;
                            case 1:
                                break;
                        }
                    } else {
                        if (!is_null($pwd) && (string)$result['USER_PASSWORD'] != $pwd) {
                            // INCORRECT PASSWORD
                            throw new UserIncorrectPasswordException('Le mot de passe est incorrect');
                        } else {
                            if (is_null($pwd)) {
                                switch ($userType) {
                                    case 0:
                                        return new UserCli($userId);
                                        break;
                                    case 1:
                                        break;
                                }
                            }
                        }
                    }
                } else {
                    // USER NOT EXIST
                    throw new UserNotExistException("L'utilisateur n'existe pas");
                }
            }
            return null;
        }

        /**
         * Load user from id
         * @param int $id id
         * @return User|null
         */
        public static function loadUserById(int $id): ?User
        {
            try {
                $bdd = Database::getDB();
                $request = $bdd->prepare('SELECT USER_ID, USER_TYPE FROM USER WHERE USER_ID = :id');
                $request->bindValue(':id', $id);

                if ($request->execute()) {
                    $result = $request->fetch();
                    if (!empty($result)) {
                        $userId = (int)$result['USER_ID'];
                        $userType = (int)$result['USER_TYPE'];

                        switch ($userType) {
                            case 0:
                                return new UserCli($userId);
                        }
                    }
                }

                return null;
            } catch (UserNotExistException) {
                return null;
            }
        }

        /**
         * Check if the user profile is signaled and invalidate
         * @return bool
         */
        public function isValid(): bool
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare('SELECT USER_VALID FROM USER WHERE USER_ID = :id');
            $request->bindValue(':id', $this->id);
            if ($request->execute()) {
                $result = $request->fetch();
                return ((int)$result['USER_VALID'] == 1);
            }
            return false;
        }

        /**
         * Check if the user is deleted
         * @return bool
         */
        public function isDeleted(): bool
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare('SELECT USER_DATETIME_DELETE FROM USER WHERE USER_ID = :id');
            $request->bindValue(':id', $this->id);
            if ($request->execute()) {
                $result = $request->fetch();
                if (!is_null($result['USER_DATETIME_DELETE'])) {
                    $this->userDatetimeDelete = new Date(strtotime($this->userDatetimeDelete));
                    return true;
                }
            }
            return false;
        }

        /**
         * Check if user have a validate account
         * @return bool true if validate, false is not
         */
        public function isValidate(): bool
        {
            if (!is_null($this->id)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare('SELECT user_validation FROM USER WHERE user_id = :id');

                $request->bindValue(':id', $this->id);

                if ($request->execute()) {
                    $result = $request->fetch();
                    return ($result['user_validation'] == "1");
                }
            }

            return false;
        }


        /**
         * Check if email address login exist
         * @param $email string e-mail of user
         * @return int|null id if user exist, null is not
         */
        public static function emailExist(string $email): ?int
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT USER_ID FROM USER WHERE user_email = :email');

            $request->bindValue(':email', $email);

            if ($request->execute()) {
                $result = $request->fetch();

                if (isset($result['USER_ID']) && !empty($result['USER_ID'])) {
                    return $result['USER_ID'];
                }
            }

            return null;
        }

        /**
         * Get distance between the user and an event
         * @param Event $event event
         * @return float distance
         */
        public function getDistance(Event $event): float
        {
            return $this->location->getDistance($event->getLocation());
        }

        /**
         * Check if an user is equal with this user
         * @param User $user user for check
         * @return bool
         */
        public function equals(User $user): bool
        {
            return ($this->id == $user->getID());
        }


        /**
         * Get the number of events that the user has participated
         * @return int
         */
        public function getNumbEventsParticipation(): int
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(event_id) as nbrEvent FROM PARTICIPATE JOIN EVENT USING(event_id) WHERE PARTICIPATE.user_id = :userId  AND PARTICIPATE.PART_DATETIME_SEND is not null AND PARTICIPATE.PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null AND event_datetime_end < sysdate()  order by event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if ($request->execute()) {
                $result = $request->fetch();

                return $result['nbrEvent'];
            }

            return 0;
        }

        /**
         * Get the events that the user has participated
         * @param $level int Level of displaying data
         * @return iterable|null list of event which user participated
         */
        public function getEventsParticipation(int $level = 0): ?iterable
        {
            $bdd = Database::getDB();

            if ($level == 1) {
                $SQL = 'OR ( event_circle = 2 AND ( event_guest_only = 0 OR event_guest_only is null ) )';
            } else {
                if ($level == 0) {
                    $SQL = '';
                }
            }
            $request = $bdd->prepare(
                'SELECT distinct PARTICIPATE.event_id, event_datetime_begin FROM PARTICIPATE LEFT JOIN EVENT ON PARTICIPATE.event_id = EVENT.event_id LEFT JOIN GUEST  ON PARTICIPATE.user_id = GUEST.user_id AND PARTICIPATE.event_id = GUEST.event_id WHERE PARTICIPATE.user_id = :userId AND PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null AND ( event_circle = 1 OR ( event_circle = 2 AND event_guest_only = 1  AND  ( GUEST.GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null ) ) ' . $SQL . ' ) AND event_datetime_end < sysdate() ORDER BY event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if ($request->execute()) {
                $events = array();

                while ($result = $request->fetch()) {
                    $events[] = new Event($result['event_id']);
                }

                return $events;
            }

            return null;
        }

        /**
         * Get the number of events that the user has organized
         * @return int
         */
        public function getNumbEventsOrganisation(): int
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(event_id) as nbrEvent FROM EVENT WHERE user_id = :userId AND event_datetime_end < sysdate() order by event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if ($request->execute()) {
                $result = $request->fetch();

                return $result['nbrEvent'];
            }

            return 0;
        }

        public function getOrganizedEvents(UserCli $userThatAsk, int $level = 0): ?iterable
        {
            $bdd = Database::getDB();
            if ($userThatAsk->equals($this)) {
                if ($level == 1) {
                    $SQL2 = 'OR ( event_circle = 2 AND ( event_guest_only = 0 OR event_guest_only is null ) )';
                } else {
                    if ($level == 0) {
                        $SQL2 = '';
                    }
                }
                $SQL1 = "AND ( event_circle = 1 OR ( event_circle = 2 AND event_guest_only = 1  AND  ( guest_datetime_send is not null AND guest_datetime_delete is null ) ) " . $SQL2 . " ) ";
            } else {
                $SQL1 = "";
            }
            $request = $bdd->prepare(
                'SELECT distinct EVENT.event_id, event_datetime_begin FROM EVENT LEFT JOIN GUEST  ON EVENT.event_id = GUEST.event_id WHERE EVENT.user_id = :userId ' . $SQL1 . ' AND event_datetime_end < sysdate() ORDER BY event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if ($request->execute()) {
                $events = array();


                while ($result = $request->fetch()) {
                    try {
                        $events[] = new Event($result['event_id']);
                    } catch (EventNotExistException|EventDeletedException|EventSignaledException $e) {
                    }
                }

                return $events;
            }


            return null;
        }

        /**
         * Get the friends list of the user
         * @return iterable|null friends list
         */
        public function getFriends(): ?iterable
        {
            $bdd = Database::getDB();

            $SQL = 'SELECT user_id_1 as user_id FROM FRIENDS WHERE user_id = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null UNION SELECT user_id FROM FRIENDS WHERE user_id_1 = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null';
            $request = $bdd->prepare(
                'SELECT user_id FROM (' . $SQL . ') tab LEFT JOIN USER USING(user_id) LEFT JOIN META_USER_CLI USING(user_id) LEFT JOIN META_USER_PRO USING(user_id) WHERE USER.USER_DATETIME_DELETE is null AND USER.USER_VALID = 1 ORDER BY META_USER_CLI.cli_lastname ASC, META_USER_PRO.PRO_NAME ASC'
            );
            $request->bindValue(':userId1', $this->getID());

            if ($request->execute()) {
                $users = array();

                while ($result = $request->fetch()) {
                    try {
                        $users[] = User::loadUserById($result['user_id']);
                    } catch (UserNotExistException|UserDeletedException|UserSignaledException $e) {
                    }
                }

                return $users;
            }

            return null;
        }

        /**
         * Get the number of friends of the user
         * @return int number of friends
         */
        public function getNumbFriends(): int
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as nbrFriend FROM FRIENDS JOIN USER user1 USING(USER_ID) JOIN USER user2 ON FRIENDS.USER_ID_1 = user2.USER_ID WHERE user1.USER_DATETIME_DELETE is null AND user1.USER_VALID = 1 AND user2.USER_DATETIME_DELETE is null AND user2.USER_VALID = 1 AND ( FRIENDS.user_id = :userId1 OR FRIENDS.user_id_1 = :userId1 ) AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());

            if ($request->execute()) {
                $result = $request->fetch();

                return $result['nbrFriend'];
            }

            return 0;
        }

        /**
         * Check if an user is a friend of this user
         * @param User $user an user
         * @return bool|null null if request breaking
         */
        public function isFriend(User $user): ?bool
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as exist FROM FRIENDS WHERE ( ( user_id = :userId1 AND user_id_1 = :userId2 ) OR ( user_id = :userId2 AND user_id_1 = :userId1 ) ) AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if ($request->execute()) {
                $result = $request->fetch();

                if ($result['exist'] > 0) {
                    return true;
                }

                return false;
            }

            return null;
        }

        /**
         * Send a friend request at an user
         * @param User $user an user
         * @return bool
         */
        public function sendFriendRequest(User $user): bool
        {
            if (!$this->isFriend($user) && !$this->isFriendWait($user) && !$this->equals($user)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare(
                    'INSERT INTO FRIENDS(user_id, user_id_1, fri_datetime_demand) VALUES(:userId1, :userId2, sysdate())'
                );
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                return $request->execute();
            }

            return false;
        }

        /**
         * Delete friendship
         * @param User $user
         * @return bool
         */
        public function unsetFriend(User $user): bool
        {
            if ($this->isFriend($user) && !$this->equals($user)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare(
                    'UPDATE FRIENDS SET fri_datetime_delete = sysdate() WHERE ((user_id = :userId2 AND user_id_1 = :userId1) OR (user_id = :userId1 AND user_id_1 = :userId2)) AND fri_datetime_accept is not null AND fri_datetime_delete is null'
                );
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                return $request->execute();
            }

            return false;
        }
    }
}