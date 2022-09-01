<?php

namespace Domain\UseCases\Login;

use Domain\Entities\User;
use Domain\Exceptions\NotAuthorizedException;
use Domain\Exceptions\UserNotExistException;
use Domain\Exceptions\ValidationErrorMessages;
use Domain\Exceptions\ValidationException;
use Domain\Interfaces\IAuthenticationContext;
use Domain\Interfaces\IUserRepository;
use Domain\Services\AccountService\Requests\LoginRequest;

final class LoginUseCase
{


    public function __construct(
        private readonly IUserRepository $userRepository,
        private readonly IAuthenticationContext $authenticationContext
    ) {
    }

    /**
     * @throws UserNotExistException
     * @throws ValidationException
     * @throws NotAuthorizedException
     */
    public function handle(LoginRequest $request): User
    {
        if ($this->authenticationContext->getConnectedUser() != null) {
            throw new NotAuthorizedException("an user is already logged");
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(ValidationErrorMessages::INCORRECT_EMAIL);
        }
        $hashedPassword = md5($request->password);
        return $this->getUserByEmailAndPasswordOrThrow($request->email, $hashedPassword);
    }

    /**
     * @throws UserNotExistException
     */
    private function getUserByEmailAndPasswordOrThrow(string $email, string $password)
    {
        $retrievedUser = $this->userRepository->getUserByEmailAndPassword($email, $password);
        if (is_null($retrievedUser)) {
            throw new UserNotExistException();
        }
        return $retrievedUser;
    }
}