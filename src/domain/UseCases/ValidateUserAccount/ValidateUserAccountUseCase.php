<?php

namespace Domain\UseCases\ValidateUserAccount;

use Domain\Entities\User;
use Domain\Exceptions\IncorrectValidationTokenException;
use Domain\Exceptions\NonConnectedUserException;
use Domain\Exceptions\UserAlreadyValidatedException;
use Domain\Interfaces\IAuthenticationContext;
use Domain\Interfaces\IUserRepository;

class ValidateUserAccountUseCase
{

    public function __construct(
        private readonly IAuthenticationContext $authenticationContext,
        private readonly IUserRepository $userRepository
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