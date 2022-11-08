<?php

namespace Business\UseCases\Login;

use Business\Entities\User;
use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\UserRepositoryInterface;

final class LoginUseCase
{


    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthenticationContextInterface $authenticationContext
    ) {
    }

    /**
     * @throws UserNotExistException
     * @throws ValidationException
     * @throws NotAuthorizedException
     */
    public function handle(LoginRequestInterface $request): User
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