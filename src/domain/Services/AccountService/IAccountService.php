<?php


namespace Domain\Services\AccountService
{


    use Domain\Entities\User;
    use Domain\Exceptions\BadAccountValidationTokenException;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\DataNotSavedException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Exceptions\TemplateLoadingException;
    use Domain\Exceptions\UserAlreadyExistException;
    use Domain\Exceptions\UserAlreadyValidatedException;
    use Domain\Exceptions\UserIncorrectPasswordException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Services\AccountService\Requests\LoginRequest;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use Domain\Services\AccountService\Requests\SignUpRequest;
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
         * Login user from login request
         * @param LoginRequest $loginRequest
         * @throws BadArgumentException
         * @throws UserIncorrectPasswordException
         * @throws UserNotExistException
         */
        public function login(LoginRequest $loginRequest): User;

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
         * @throws DataNotSavedException
         * @throws Exception
         */
        public function sendNewValidationToken(int $userId): void;

        /**
         * Sign up visitor
         * @param SignUpRequest $signUpRequest
         * @return User
         * @throws UserAlreadyExistException
         * @throws BadArgumentException
         * @throws Exception
         */
        public function signUp(SignUpRequest $signUpRequest): User;

        /**
         * Validate account of user
         * @param int $userId
         * @param ValidateAccountRequest $validateAccountRequest
         * @throws UserAlreadyValidatedException
         * @throws BadAccountValidationTokenException
         * @throws DataNotSavedException
         */
        public function validateAccount(int $userId, ValidateAccountRequest $validateAccountRequest): void;
    }
}
