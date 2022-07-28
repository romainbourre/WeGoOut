<?php

namespace Domain\Entities
{

    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserSignaledException;
    use System\Librairies\Database;
    use Domain\Exceptions\UserNotExistException;

    /**
     * Class UserCli
     * Represent lambda user
     * @package App\Lib
     */
    class UserCli extends User
    {

        private $lastName;
        private $firstName;
        private $description;
        private $sex;
        private $relationship;

        /**
         * UserCli constructor.
         * @param int $id
         * @throws UserNotExistException
         */
        public function __construct(int $id)
        {
            $this->load($id);
        }

        /**
         * Load data of the  client user from database
         * @param int $id id of user
         * @throws UserNotExistException
         */
        protected function load(int $id): void
        {
            // LOAD GENERALS DATA OF USER
            parent::load($id);
            // RECOVER DATABASE
            $bdd = Database::getDB();
            // COMPOSE DATABASE REQUEST
            $request = $bdd->prepare('SELECT * FROM META_USER_CLI WHERE user_id = :id');
            $request->bindValue(':id', $id);
            // EXECUTE DATABASE REQUEST
            if ($request->execute())
            {
                $result = $request->fetch();
                if (!empty($result))
                {
                    $this->lastName = (string)$result['CLI_LASTNAME'];
                    $this->firstName = (string)$result['CLI_FIRSTNAME'];
                    $this->description = (string)$result['CLI_DESCRIPTION'];
                    $this->sex = (string)$result['CLI_SEX'];
                    $this->relationship = (string)$result['CLI_RELATIONSHIP'];
                }
                else
                {
                    throw new UserNotExistException("L'utilisateur n'existe pas");
                }
            }
        }

        /**
         * Get last name of user
         * @return string lastname
         */
        public function getLastname(): string
        {
            return $this->lastName;
        }

        /**
         * Get first name of user
         * @return mixed first name
         */
        public function getFirstname(): string
        {
            return $this->firstName;
        }

        /**
         * Get the description of the user
         * @return string description
         */
        public function getDescription(): string
        {
            return (string)$this->description;
        }

        /**
         * Get sex of the user
         * @return string|null sex
         */
        public function getSex(): ?string
        {
            switch ($this->sex)
            {
                case "H":
                    return "Homme";
                    break;
                case "F":
                    return "Femme";
                    break;
            }

            return null;
        }

        /**
         * Get relationship of the user
         * @return string|null relationship
         */
        public function getRelationship(): ?string
        {
            switch ($this->relationship)
            {
                case 'M':
                    return "Marié";
                    break;
                case 'C':
                    return "Célibataire";
                    break;
                case 'R':
                    return "En couple";
                    break;
            }

            return null;
        }


        /**
         * Get the friends list who asked the user
         * @return iterable|null friends who asked
         * @throws UserNotExistException
         */
        public function getFriendsASkMe(): ?iterable
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT user_id FROM FRIENDS WHERE user_id_1 = :userId1 AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null');
            $request->bindValue(':userId1', $this->getID());

            if ($request->execute())
            {

                $users = array();

                while ($result = $request->fetch())
                {

                    $users[] = User::loadUserById($result['user_id']);

                }

                return $users;

            }

            return null;

        }


        /**
         * Check if exist a wait friend request between an user and this user
         * @param User $user an user
         * @return bool|null null if request breaking
         */
        public function isFriendWait(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as exist FROM FRIENDS WHERE ( ( user_id = :userId1 AND user_id_1 = :userId2 ) OR ( user_id = :userId2 AND user_id_1 = :userId1 ) ) AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null ');
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if ($request->execute())
            {

                $result = $request->fetch();

                if ($result['exist'] > 0) return true;

                return false;

            }

            return null;

        }

        /**
         * Check if this user have send request to an user
         * @param User $user an user
         * @return bool|null
         */
        public function isFriendWaitFromMe(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as exist FROM FRIENDS WHERE user_id = :userId1 AND user_id_1 = :userId2  AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null ');
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if ($request->execute())
            {

                $result = $request->fetch();

                if ($result['exist'] > 0) return true;

                return false;

            }

            return null;

        }

        /**
         * Check if an user have send request to this user
         * @param User $user an user
         * @return bool|null
         */
        public function isFriendWaitFromUser(User $user): ?bool
        {

            $bdd = Database::getDB();

            $request = $bdd->prepare('SELECT count(*) as exist FROM FRIENDS WHERE user_id = :userId2 AND user_id_1 = :userId1  AND fri_datetime_demand is not null AND fri_datetime_accept is null AND fri_datetime_delete is null ');
            $request->bindValue(':userId1', $this->getID());
            $request->bindValue(':userId2', $user->getID());

            if ($request->execute())
            {

                $result = $request->fetch();

                if ($result['exist'] > 0) return true;

                return false;

            }

            return null;

        }


        /**
         * Delete friend request from this user to an user
         * @param User $user an user
         * @return bool
         */
        public function unsetFriendRequest(User $user): bool
        {

            if ($this->isFriendWaitFromMe($user) && !$this->equals($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE FRIENDS SET fri_datetime_delete = sysdate() WHERE user_id = :userId1 AND user_id_1 = :userId2 AND fri_datetime_accept is null');
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                return $request->execute();

            }

            return false;

        }

        /**
         * Refuse friend request from an user
         * @param User $user an user
         * @return bool
         */
        public function refuseFriend(User $user): bool
        {

            if ($this->isFriendWaitFromUser($user) && !$this->equals($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE FRIENDS SET fri_datetime_delete = sysdate() WHERE user_id = :userId2 AND user_id_1 = :userId1 AND fri_datetime_accept is null');
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                return $request->execute();

            }

            return false;

        }

        /**
         * Accept friend request from an user
         * @param User $user an user
         * @return bool
         */
        public function acceptFriend(User $user): bool
        {

            if ($this->isFriendWaitFromUser($user) && !$this->equals($user))
            {

                $bdd = Database::getDB();

                $request = $bdd->prepare('UPDATE FRIENDS SET fri_datetime_accept = sysdate() WHERE user_id = :userId2 AND user_id_1 = :userId1 AND fri_datetime_accept is null');
                $request->bindValue(':userId1', $this->getID());
                $request->bindValue(':userId2', $user->getID());

                return $request->execute();

            }

            return false;

        }
    }
}