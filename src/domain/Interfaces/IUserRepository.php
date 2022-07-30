<?php


namespace Domain\Interfaces
{


    use Domain\Entities\User;
    use Domain\Exceptions\DatabaseErrorException;

    interface IUserRepository
    {
        public function setPassword(string $email, string $pwd): bool;

        /**
         * Add user
         * @param array $data
         * @return User
         * @throws DatabaseErrorException
         */
        public function addUser(array $data): User;

        public function getValidationCode(int $id);

        /**
         * Set user's account as valid account
         * @param int $userId
         * @throws DatabaseErrorException
         */
        public function setAccountAsValid(int $userId): void;

        /**
         * Set user's account validation token
         * @param int $userId id of user
         * @param string $token new validation token
         * @return void
         * @throws DatabaseErrorException
         */
        public function setValidationToken(int $userId, string $token): void;
    }
}