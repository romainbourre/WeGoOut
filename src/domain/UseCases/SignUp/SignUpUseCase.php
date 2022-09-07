<?php

namespace Domain\UseCases\SignUp;

use Domain\Entities\User;
use Domain\Exceptions\UserAlreadyExistException;
use Domain\Exceptions\ValidationErrorMessages;
use Domain\Exceptions\ValidationException;
use Domain\Interfaces\DateTimeProviderInterface;
use Domain\Interfaces\IEmailSender;
use Domain\Interfaces\IUserRepository;
use Domain\Interfaces\PasswordEncoderInterface;
use Domain\Interfaces\TokenProviderInterface;
use Domain\ValueObjects\FrenchDate;
use Domain\ValueObjects\Location;
use Exception;


final class SignUpUseCase
{


    public function __construct(
        private readonly PasswordEncoderInterface $passwordEncoder,
        private readonly DateTimeProviderInterface $dateTimeProvider,
        private readonly TokenProviderInterface $tokenProvider,
        private readonly IEmailSender $emailSender,
        private readonly IUserRepository $userRepository
    ) {
    }

    /**
     * @throws ValidationException
     * @throws UserAlreadyExistException
     * @throws Exception
     */
    public function handle(SignUpRequest $request): User
    {
        $encodedPassword = $this->encodePassword($request->password);
        $userToSave = $this->createUserFromRequest($request);
        $this->ensureThatUserIsMajor($userToSave);
        $savedUser = $this->saveUserWithPasswordIfNotExist($userToSave, $encodedPassword);
        $this->sendValidationTokenToUser($savedUser);
        return $savedUser;
    }

    /**
     * @throws ValidationException
     */
    private function encodePassword(string $password): string
    {
        if (strlen($password) < 6) {
            throw new ValidationException(ValidationErrorMessages::INCORRECT_PASSWORD);
        }
        return $this->passwordEncoder->encode($password);
    }

    /**
     * @throws Exception
     */
    private function createUserFromRequest(SignUpRequest $request): User
    {
        $expectedLocation = new Location($request->postalCode, $request->city, $request->latitude, $request->longitude);
        return new User(
            id: null,
            email: $request->email,
            firstname: $request->firstname,
            lastname: $request->lastname,
            picture: null,
            description: null,
            birthDate: FrenchDate::parse($request->birthDate),
            location: $expectedLocation,
            validationToken: $this->tokenProvider->getNext(),
            genre: $request->genre,
            createdAt: new FrenchDate($this->dateTimeProvider->getNext()->getTimestamp()),
            deletedAt: null
        );
    }

    /**
     * @throws ValidationException
     */
    private function ensureThatUserIsMajor(User $user): void
    {
        $currentDate = $this->dateTimeProvider->getNext();
        $userBirthDate = $user->birthDate->value;
        $interval = $currentDate->diff($userBirthDate);
        if ($interval->y < 18) {
            throw new ValidationException(ValidationErrorMessages::INCORRECT_BIRTHDATE);
        }
    }

    /**
     * @throws UserAlreadyExistException
     */
    private function saveUserWithPasswordIfNotExist(User $user, string $password): User
    {
        if ($this->userRepository->isEmailExist($user->email)) {
            throw new UserAlreadyExistException();
        }
        return $this->userRepository->addUserWithPassword($user, $password);
    }

    private function sendValidationTokenToUser(User $user): void
    {
        $this->emailSender->sendValidationTokenOfUser($user);
    }
}