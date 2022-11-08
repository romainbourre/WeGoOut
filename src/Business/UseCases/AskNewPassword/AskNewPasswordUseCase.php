<?php

namespace Business\UseCases\AskNewPassword;

use Business\Exceptions\ValidationException;
use Business\Ports\EmailSenderInterface;
use Business\Ports\PasswordEncoderInterface;
use Business\Ports\PasswordGeneratorInterface;
use Business\Ports\UserRepositoryInterface;
use Business\ValueObjects\Email;

class AskNewPasswordUseCase
{


    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordGeneratorInterface $passwordGenerator,
        private readonly PasswordEncoderInterface $passwordEncoder,
        private readonly EmailSenderInterface $emailSender
    ) {
    }

    /**
     * @throws ValidationException
     */
    public function handle(AskNewPasswordRequest $request): void
    {
        $user = $this->userRepository->getUserByEmail(Email::from($request->email));
        if ($user == null) {
            return;
        }
        $nextPassword = $this->passwordGenerator->generate();
        $encodedPassword = $this->passwordEncoder->encode($nextPassword);
        $this->userRepository->setPassword($user->id, $encodedPassword);
        $this->emailSender->sendNewPasswordToUser($user, $nextPassword);
    }
}