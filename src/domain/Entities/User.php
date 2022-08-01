<?php

namespace Domain\Entities
{

    use Domain\Exceptions\DatabaseErrorException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserIncorrectPasswordException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\ValueObjects\FrenchDate;
    use Domain\ValueObjects\Location;
    use System\Librairies\Database;

    class User
    {

        private const DEFAULT_PICTURE = "/assets/img/33aeda9.png";


        public function __construct(
            public readonly int $id,
            public readonly string $email,
            public readonly string $firstname,
            public readonly string $lastname,
            public readonly ?string $picture,
            public readonly ?string $description,
            private readonly FrenchDate $birthDate,
            private readonly Location $location,
            private readonly ?string $validationToken,
            private readonly string $genre,
            private readonly FrenchDate $createdAt,
            private readonly ?FrenchDate $deletedAt,
        ) {
        }

        public function getID(): int
        {
            return $this->id;
        }

        public function getName(string $type = null): ?string
        {
            $type = strtolower($type);
            return match ($type) {
                "full" => $this->firstname . " " . $this->lastname,
                default => $this->firstname,
            };
        }

        public function getEmail(): string
        {
            return $this->email;
        }

        public function getPicture(): string
        {
            if (is_null($this->picture)) {
                return self::DEFAULT_PICTURE;
            }
            return $this->picture;
        }

        public function getLocation(): Location
        {
            return $this->location;
        }

        public function getBirthDate(): FrenchDate
        {
            return $this->birthDate;
        }

        public function computeGenre(): ?string
        {
            return match ($this->genre) {
                "H" => "Homme",
                "F" => "Femme",
                default => null,
            };
        }

        public function isDeleted(): bool
        {
            return is_null($this->deletedAt);
        }

        public function isValidate(): bool
        {
            return $this->validationToken == "1";
        }

        public function getDistance(Event $event): float
        {
            return $this->location->getDistance($event->getLocation());
        }

        public function equals(User $user): bool
        {
            return ($this->id == $user->getID());
        }

        /**
         * @throws DatabaseErrorException
         * @throws UserNotExistException
         */
        public function getFriendsASkMe(): array
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT user_id FROM FRIENDS WHERE user_id_1 = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null'
            );
            $request->bindValue(':userId1', $this->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $users = array();
            while ($result = $request->fetch()) {
                $users[] = User::load($result['user_id']);
            }
            return $users;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function isFriendWait(User $user): bool
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as exist FROM FRIENDS WHERE ( ( user_id = :userId1 AND user_id_1 = :userId2 ) OR ( user_id = :userId2 AND user_id_1 = :userId1 ) ) AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['exist'] > 0;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function isFriendWaitFromMe(User $user): bool
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as exist FROM FRIENDS WHERE user_id = :userId1 AND user_id_1 = :userId2  AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['exist'] > 0;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function isFriendWaitFromUser(User $user): ?bool
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as exist FROM FRIENDS WHERE user_id = :userId2 AND user_id_1 = :userId1  AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['exist'] > 0;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function unsetFriendRequest(User $user): bool
        {
            if ($this->isFriendWaitFromMe($user) && !$this->equals($user)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare(
                    'UPDATE FRIENDS SET fri_datetime_delete = sysdate() WHERE user_id = :userId1 AND user_id_1 = :userId2 AND fri_datetime_accept is null'
                );
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                if (!$request->execute()) {
                    throw new DatabaseErrorException();
                }

                return true;
            }
            return false;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function refuseFriend(User $user): bool
        {
            if ($this->isFriendWaitFromUser($user) && !$this->equals($user)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare(
                    'UPDATE FRIENDS SET fri_datetime_delete = sysdate() WHERE user_id = :userId2 AND user_id_1 = :userId1 AND fri_datetime_accept is null'
                );
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                if (!$request->execute()) {
                    throw new DatabaseErrorException();
                }

                return true;
            }
            return false;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function acceptFriend(User $user): bool
        {
            if ($this->isFriendWaitFromUser($user) && !$this->equals($user)) {
                $bdd = Database::getDB();

                $request = $bdd->prepare(
                    'UPDATE FRIENDS SET fri_datetime_accept = sysdate() WHERE user_id = :userId2 AND user_id_1 = :userId1 AND fri_datetime_accept is null'
                );
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                if (!$request->execute()) {
                    throw new DatabaseErrorException();
                }

                return true;
            }
            return false;
        }

        /**
         * @throws DatabaseErrorException
         * @throws UserIncorrectPasswordException
         * @throws UserNotExistException
         */
        public static function loadUserByEmail(string $email, string $password = null): ?User
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare('SELECT USER_ID, USER_PASSWORD FROM USER WHERE USER_EMAIL = :id');
            $request->bindValue(':id', $email);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            if (!$result) {
                throw new UserNotExistException("L'utilisateur n'existe pas");
            }

            $userId = (int)$result['USER_ID'];

            if (is_null($password)) {
                return User::load($userId);
            }

            $isNotCorrectPassword = $result['USER_PASSWORD'] != $password;
            if ($isNotCorrectPassword) {
                throw new UserIncorrectPasswordException('Le mot de passe est incorrect');
            }

            return User::load($userId);
        }

        /**
         * @throws DatabaseErrorException
         */
        public static function emailExist(string $email): bool
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare('SELECT USER_ID FROM USER WHERE user_email = :email');
            $request->bindValue(':email', $email);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return isset($result['USER_ID']) && !empty($result['USER_ID']);
        }

        /**
         * @throws DatabaseErrorException
         */
        public function getNumberOfEventsWhichUserParticipate(): int
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare(
                'SELECT count(event_id) as nbrEvent FROM PARTICIPATE JOIN EVENT USING(event_id) WHERE PARTICIPATE.user_id = :userId  AND PARTICIPATE.PART_DATETIME_SEND is not null AND PARTICIPATE.PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null AND event_datetime_end < sysdate()  order by event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['nbrEvent'];
        }

        /**
         * @throws EventNotExistException
         * @throws DatabaseErrorException
         */
        public function getEventsWhichUserParticipate(int $level = 0): array
        {
            $bdd = Database::getDB();

            if ($level == 1) {
                $SQL = 'OR ( event_circle = 2 AND ( event_guest_only = 0 OR event_guest_only is null ) )';
            } elseif ($level == 0) {
                $SQL = '';
            }
            $request = $bdd->prepare(
                'SELECT distinct PARTICIPATE.event_id, event_datetime_begin FROM PARTICIPATE LEFT JOIN EVENT ON PARTICIPATE.event_id = EVENT.event_id LEFT JOIN GUEST  ON PARTICIPATE.user_id = GUEST.user_id AND PARTICIPATE.event_id = GUEST.event_id WHERE PARTICIPATE.user_id = :userId AND PART_DATETIME_ACCEPT is not null AND PART_DATETIME_DELETE is null AND ( event_circle = 1 OR ( event_circle = 2 AND event_guest_only = 1  AND  ( GUEST.GUEST_DATETIME_SEND is not null AND GUEST_DATETIME_DELETE is null ) ) ' . $SQL . ' ) AND event_datetime_end < sysdate() ORDER BY event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $events = array();
            while ($result = $request->fetch()) {
                $events[] = new Event($result['event_id']);
            }
            return $events;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function getNumberOfEventsWhichUserOrganize(): int
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare(
                'SELECT count(event_id) as nbrEvent FROM EVENT WHERE user_id = :userId AND event_datetime_end < sysdate() order by event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['nbrEvent'];
        }

        /**
         * @throws DatabaseErrorException
         */
        public function getEventsWhichUserOrganize(User $userThatAsk, int $level = 0): ?iterable
        {
            $bdd = Database::getDB();
            if ($userThatAsk->equals($this)) {
                if ($level == 1) {
                    $SQL2 = 'OR ( event_circle = 2 AND ( event_guest_only = 0 OR event_guest_only is null ) )';
                } elseif ($level == 0) {
                    $SQL2 = '';
                }
                $SQL1 = "AND ( event_circle = 1 OR ( event_circle = 2 AND event_guest_only = 1  AND  ( guest_datetime_send is not null AND guest_datetime_delete is null ) ) " . $SQL2 . " ) ";
            } else {
                $SQL1 = "";
            }
            $request = $bdd->prepare(
                'SELECT distinct EVENT.event_id, event_datetime_begin FROM EVENT LEFT JOIN GUEST  ON EVENT.event_id = GUEST.event_id WHERE EVENT.user_id = :userId ' . $SQL1 . ' AND event_datetime_end < sysdate() ORDER BY event_datetime_begin ASC'
            );
            $request->bindValue(':userId', $this->id);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $events = array();
            while ($result = $request->fetch()) {
                try {
                    $events[] = new Event($result['event_id']);
                } catch (EventNotExistException|EventDeletedException|EventSignaledException $e) {
                }
            }
            return $events;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function getFriends(): ?iterable
        {
            $bdd = Database::getDB();
            $SQL = 'SELECT user_id_1 as user_id FROM FRIENDS WHERE user_id = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null UNION SELECT user_id FROM FRIENDS WHERE user_id_1 = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null';
            $request = $bdd->prepare(
                'SELECT user_id FROM (' . $SQL . ') tab LEFT JOIN USER USING(user_id) LEFT JOIN META_USER_CLI USING(user_id) LEFT JOIN META_USER_PRO USING(user_id) WHERE USER.USER_DATETIME_DELETE is null AND USER.USER_VALID = 1 ORDER BY META_USER_CLI.cli_lastname ASC, META_USER_PRO.PRO_NAME ASC'
            );
            $request->bindValue(':userId1', $this->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $users = array();
            while ($result = $request->fetch()) {
                try {
                    $users[] = User::load($result['user_id']);
                } catch (UserNotExistException|UserDeletedException|UserSignaledException $e) {
                }
            }
            return $users;
        }

        /**
         * @throws DatabaseErrorException
         */
        public function getNumberOfFriends(): int
        {
            $bdd = Database::getDB();
            $request = $bdd->prepare(
                'SELECT count(*) as nbrFriend FROM FRIENDS JOIN USER user1 USING(USER_ID) JOIN USER user2 ON FRIENDS.USER_ID_1 = user2.USER_ID WHERE user1.USER_DATETIME_DELETE is null AND user1.USER_VALID = 1 AND user2.USER_DATETIME_DELETE is null AND user2.USER_VALID = 1 AND ( FRIENDS.user_id = :userId1 OR FRIENDS.user_id_1 = :userId1 ) AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['nbrFriend'];
        }

        /**
         * @throws DatabaseErrorException
         */
        public function isFriend(User $user): ?bool
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT count(*) as exist FROM FRIENDS WHERE ( ( user_id = :userId1 AND user_id_1 = :userId2 ) OR ( user_id = :userId2 AND user_id_1 = :userId1 ) ) AND fri_datetime_demand is not null AND fri_datetime_accept is not null AND fri_datetime_delete is null '
            );
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return $result['exist'] > 0;
        }

        /**
         * @throws DatabaseErrorException
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

                if (!$request->execute()) {
                    throw new DatabaseErrorException();
                }

                return true;
            }
            return false;
        }

        /**
         * @throws DatabaseErrorException
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

                if (!$request->execute()) {
                    throw new DatabaseErrorException();
                }

                return true;
            }
            return false;
        }

        /**
         * @throws DatabaseErrorException
         * @throws UserNotExistException
         */
        public static function load(int $id): User
        {
            $bdd = Database::getDB();

            $request = $bdd->prepare(
                'SELECT * FROM META_USER_CLI JOIN USER U on META_USER_CLI.USER_ID = U.USER_ID WHERE META_USER_CLI.USER_ID = :id'
            );
            $request->bindValue(':id', $id);
            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            if (empty($result)) {
                throw new UserNotExistException("L'utilisateur n'existe pas");
            }

            return self::mapDataToUser($result);
        }

        private static function mapDataToUser(array $result): User
        {
            $locationOfLoadedUser = new Location(
                (double)$result['USER_LOCATION_LAT'],
                (double)$result['USER_LOCATION_LNG']
            );

            $locationOfLoadedUser->setCity($result['USER_LOCATION_CITY']);

            return new User(
                id: $result['USER_ID'],
                email: $result['USER_EMAIL'],
                firstname: $result['CLI_FIRSTNAME'],
                lastname: $result['CLI_LASTNAME'],
                picture: $result['USER_PROFILE_PICTURE'],
                description: $result['CLI_DESCRIPTION'],
                birthDate: new FrenchDate(strtotime((string)$result['USER_DATE_BIRTH'])),
                location: $locationOfLoadedUser,
                validationToken: $result['USER_VALIDATION'],
                genre: $result['CLI_SEX'],
                createdAt: new FrenchDate(strtotime($result['USER_DATETIME_REGISTRATION'])),
                deletedAt: new FrenchDate(strtotime($result['USER_DATETIME_DELETE']))
            );
        }
    }
}