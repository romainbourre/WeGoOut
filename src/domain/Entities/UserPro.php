<?php

namespace Domain\Entities
{

    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use System\Configuration\Librairies\Database;

    /**
     * Class UserPro
     * Represent professional user
     * @package App\Lib
     */
    class UserPro extends User
    {

        private $name;
        private $description;

        /**
         * Load data of the  professional user from database
         * @param int $id id
         * @throws UserNotExistException
         * @throws UserDeletedException
         * @throws UserSignaledException
         */
        protected function load(int $id): void
        {
            // LOAD GENERALS DATA OF USER
            parent::load($id);
            // RECOVER DATABASE
            $bdd = Database::getDB();
            // COMPOSE DATABASE REQUEST
            $request = $bdd->prepare('SELECT * FROM META_USER_PRO WHERE user_id = :id');
            $request->bindValue(':id', $id);
            // EXECUTE DATABASE REQUEST
            if ($request->execute())
            {
                $result = $request->fetch();
                if (!empty($result))
                {
                    $this->name = (string)$result['PRO_NAME'];
                    $this->description = (string)$result['CLI_DESCRIPTION'];
                }
                else
                {
                    throw new UserNotExistException("L'utilisateur n'existe pas");
                }
            }
        }

        /**
         * Get the name of professional
         * @return string name
         */
        public function get_name(): string
        {
            return (string)$this->name;
        }

        /**
         * Get the description of the professional
         * @return string description
         */
        public function get_description(): string
        {
            return (string)$this->description;
        }
    }
}