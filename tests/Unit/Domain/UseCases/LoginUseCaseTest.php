<?php

namespace Tests\Unit\Domain\UseCases;

use Domain\Exceptions\NotAuthorizedException;
use Domain\Exceptions\UserNotExistException;
use Domain\Exceptions\ValidationErrorMessages;
use Domain\Exceptions\ValidationException;
use Domain\Services\AccountService\Requests\LoginRequest;
use Domain\UseCases\Login\LoginUseCase;
use Infrastructure\InMemory\Repositories\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Contexts\DeterministAuthenticationContext;

class LoginUseCaseTest extends TestCase
{
    private readonly InMemoryUserRepository           $userRepository;
    private readonly DeterministAuthenticationContext $authenticationContext;
    private readonly LoginUseCase                     $useCase;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new InMemoryUserRepository();
        $this->authenticationContext = new DeterministAuthenticationContext();
        $this->useCase = new LoginUseCase($this->userRepository, $this->authenticationContext);
    }

    public function testThat_Given_SavedUser_Then_AskToLoginWithEmailAndPassword_Then_ReturnUser()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequest($user->email, $userPassword);
        $loggedUser = $this->useCase->handle($loginRequest);

        $this->assertEquals($user, $loggedUser);
    }

    public function testThat_Given_SavedUser_Then_AskToLoginWithEmailAndBadPassword_Then_PreventError()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequest($user->email, "badPassword");
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    public function testThat_Given_SavedUser_Then_AskToLoginWithBadEmailAndPassword_Then_PreventError()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequest("bad.email@provider.fr", $userPassword);
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    public function testThat_Given_NonSavedUser_Then_AskToLoginWithEmailAndPassword_Then_PreventError()
    {
        $loginRequest = new LoginRequest("non-existant-user@provider.fr", "password");
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    public function testThat_Given_NonSavedUser_Then_AskToLoginWithBadFormatedEmailAndPassword_Then_PreventError()
    {
        $loginRequest = new LoginRequest("non-email", "password");
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EMAIL);
        $this->useCase->handle($loginRequest);
    }

    public function testThat_Given_AlreadyConnectedUser_Then_AskToLogin_Then_PreventError()
    {
        $this->authenticationContext->setConnectedUser(UserBuilder::given()->create());
        $loginRequest = new LoginRequest("user@provider.fr", "password");
        $this->expectException(NotAuthorizedException::class);
        $this->useCase->handle($loginRequest);
    }
}