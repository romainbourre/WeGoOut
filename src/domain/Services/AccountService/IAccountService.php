<?php


namespace Domain\Services\AccountService
{


    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\DatabaseErrorException;
    use Domain\Exceptions\TemplateLoadingException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use Exception;

    interface IAccountService
    {
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
    }
}
