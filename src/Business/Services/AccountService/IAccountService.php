<?php


namespace Business\Services\AccountService
{


    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\UserNotExistException;
    use Exception;

    interface IAccountService
    {
        /**
         * Set new account validation token to user and sent it to him
         * @param int $userId
         * @throws UserNotExistException
         * @throws DatabaseErrorException
         * @throws Exception
         */
        public function sendNewValidationToken(int $userId): void;
    }
}
