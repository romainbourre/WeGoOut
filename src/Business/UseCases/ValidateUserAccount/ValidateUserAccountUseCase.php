<?php

namespace Business\UseCases\ValidateUserAccount;

use Business\Entities\User;
use Business\Exceptions\IncorrectValidationTokenException;
use Business\Exceptions\NonConnectedUserException;
use Business\Exceptions\UserAlreadyValidatedException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\UserRepositoryInterface;

class ValidateUserAccountUseCase
{

    public function __construct(
        private readonly AuthenticationContextInterface $authenticationContext,
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @throws IncorrectValidationTokenException
     * @throws UserAlreadyValidatedException
     * @throws NonConnectedUserException
     */
    public function handle(ValidateUserAccountRequest $request): void
    {
        $connectedUser = $this->getConnectedUserOrThrow();
        $this->checkAndSetAccountAsValidForUser($connectedUser, $request->token);
    }

    /**
     * @throws NonConnectedUserException
     */
    private function getConnectedUserOrThrow(): User
    {
        $connectedUser = $this->authenticationContext->getConnectedUser();
        if ($connectedUser == null) {
            throw new NonConnectedUserException();
        }
        return $connectedUser;
    }

    /**
     * @throws IncorrectValidationTokenException
     * @throws UserAlreadyValidatedException
     */
    private function checkAndSetAccountAsValidForUser(User $user, string $token): void
    {
        if ($user->validationToken === null) {
            throw new UserAlreadyValidatedException();
        }
        if ($user->validationToken != $token) {
            throw new IncorrectValidationTokenException();
        }
        $this->userRepository->setAccountAsValid($user->id);
    }
}