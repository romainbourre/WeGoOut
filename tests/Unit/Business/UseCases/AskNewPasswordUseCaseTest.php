<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryUserRepository;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\UseCases\AskNewPassword\AskNewPasswordRequest;
use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Encoders\SimplePasswordEncoder;
use Tests\Utils\Mocks\EmailSenderMockInterface;
use Tests\Utils\Providers\DeterministPasswordGeneratorInterface;

class AskNewPasswordUseCaseTest extends TestCase
{
    private readonly InMemoryUserRepository $userRepository;
    private readonly DeterministPasswordGeneratorInterface $deterministPasswordGenerator;
    private readonly EmailSenderMockInterface $emailSender;
    private readonly SimplePasswordEncoder $passwordEncoder;
    private readonly AskNewPasswordUseCase $useCase;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new InMemoryUserRepository();
        $this->emailSender = new EmailSenderMockInterface();
        $this->deterministPasswordGenerator = new DeterministPasswordGeneratorInterface();
        $this->passwordEncoder = new SimplePasswordEncoder();
        $this->useCase = new AskNewPasswordUseCase(
            $this->userRepository,
            $this->deterministPasswordGenerator,
            $this->passwordEncoder,
            $this->emailSender
        );
    }


    /**
     * @throws ValidationException
     */
    public function testThat_Given_SavedUser_When_AskToNewPasswordForEmail_Then_ReceiveLink()
    {
        $email = 'john.doe@dev.com';
        $user = UserBuilder::given()->withEmail($email)->create();
        $this->userRepository->addUserWithPassword($user, 'forgotten-password');
        $nextPassword = $this->deterministPasswordGenerator->setNext('next-password');

        $request = new AskNewPasswordRequest($email);
        $this->useCase->handle($request);

        $encodedPassword = $this->passwordEncoder->encode($nextPassword);
        $this->assertNotNull($this->userRepository->getUserByEmailAndPassword($email, $encodedPassword));
        $this->assertTrue($this->emailSender->newPasswordForUserSent($user, $nextPassword));
    }

    public function testThat_Given_BadEmail_When_AskToNewPassword_Then_PreventEmailFormatError(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EMAIL);

        $request = new AskNewPasswordRequest('john.doe.dev.com');
        $this->useCase->handle($request);
    }

    /**
     * @throws ValidationException
     */
    public function testThat_Given_UnknownEmail_When_AskToNewPassword_Then_DoNothing(): void
    {
        $email = 'john.doe@dev.com';
        $nextPassword = $this->deterministPasswordGenerator->setNext('nextPassword');

        $request = new AskNewPasswordRequest($email);
        $this->useCase->handle($request);

        $this->assertNull($this->userRepository->getUserByEmailAndPassword($email, $nextPassword));
        $this->assertTrue($this->emailSender->noNewPasswordSent());
    }
}
