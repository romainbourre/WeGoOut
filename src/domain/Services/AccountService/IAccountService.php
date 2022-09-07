<?php


namespace Domain\Services\AccountService
{


    use Domain\Exceptions\BadAccountValidationTokenException;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\DatabaseErrorException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Exceptions\TemplateLoadingException;
    use Domain\Exceptions\UserAlreadyValidatedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use Domain\Services\AccountService\Requests\ValidateAccountRequest;
    use Exception;

    interface IAccountService
    {
        /**
         * Check if account of user is validated
         * @param string $userId
         * @return bool
         * @throws ResourceNotFound
         */
        public function isValidAccount(string $userId): bool;

        /**
         * Reset password of user from email
         * @param ResetPasswordRequest $resetPasswordRequest request to reset password
         * @throws BadArgumentException
         * @throws TemplateLoadingException
         * @throws UserNotExistException
         */
        public function resetPassword(ResetPasswordRequest $resetPasswordRequest): void;

        /**
         * Set new account validation token to user and sent it to him
         * @param int $userId
         * @throws UserNotExistException
         * @throws DatabaseErrorException
         * @throws Exception
         */
        public function sendNewValidationToken(int $userId): void;

        /**
         * Validate account of user
         * @param int $userId
         * @param ValidateAccountRequest $validateAccountRequest
         * @throws UserAlreadyValidatedException
         * @throws BadAccountValidationTokenException
         * @throws DatabaseErrorException
         */
        public function validateAccount(int $userId, ValidateAccountRequest $validateAccountRequest): void;
    }
}
