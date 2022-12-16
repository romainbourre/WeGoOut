<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryUserRepository;
use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\UseCases\Login\LoginRequestInterface;
use Business\UseCases\Login\LoginUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Contexts\DeterministAuthenticationContext;

class LoginUseCaseTest extends TestCase
{
    private readonly InMemoryUserRepository $userRepository;
    private readonly DeterministAuthenticationContext $authenticationContext;
    private readonly LoginUseCase $useCase;

    public function __construct()
    {
        parent::__construct();
        $this->userRepository = new InMemoryUserRepository();
        $this->authenticationContext = new DeterministAuthenticationContext();
        $this->useCase = new LoginUseCase($this->userRepository, $this->authenticationContext);
    }

    /**
     * @throws NotAuthorizedException
     * @throws UserNotExistException
     * @throws ValidationException
     */
    public function testThat_Given_SavedUser_Then_AskToLoginWithEmailAndPassword_Then_ReturnUser()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequestInterface($user->email, $userPassword);
        $loggedUser = $this->useCase->handle($loginRequest);

        $this->assertEquals($user, $loggedUser);
    }

    /**
     * @throws NotAuthorizedException
     * @throws ValidationException
     */
    public function testThat_Given_SavedUser_Then_AskToLoginWithEmailAndBadPassword_Then_PreventError()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequestInterface($user->email, "badPassword");
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    /**
     * @throws NotAuthorizedException
     * @throws ValidationException
     */
    public function testThat_Given_SavedUser_Then_AskToLoginWithBadEmailAndPassword_Then_PreventError()
    {
        $user = UserBuilder::given()->create();
        $userPassword = "myPassword";
        $this->userRepository->addUserWithPassword($user, md5($userPassword));

        $loginRequest = new LoginRequestInterface("bad.email@provider.fr", $userPassword);
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    /**
     * @throws NotAuthorizedException
     * @throws ValidationException
     */
    public function testThat_Given_NonSavedUser_Then_AskToLoginWithEmailAndPassword_Then_PreventError()
    {
        $loginRequest = new LoginRequestInterface("non-existant-user@provider.fr", "password");
        $this->expectException(UserNotExistException::class);
        $this->useCase->handle($loginRequest);
    }

    /**
     * @throws NotAuthorizedException
     * @throws UserNotExistException
     */
    public function testThat_Given_NonSavedUser_Then_AskToLoginWithBadFormatedEmailAndPassword_Then_PreventError()
    {
        $loginRequest = new LoginRequestInterface("non-email", "password");
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EMAIL);
        $this->useCase->handle($loginRequest);
    }

    /**
     * @throws UserNotExistException
     * @throws ValidationException
     */
    public function testThat_Given_AlreadyConnectedUser_Then_AskToLogin_Then_PreventError()
    {
        $this->authenticationContext->setConnectedUser(UserBuilder::given()->create());
        $loginRequest = new LoginRequestInterface("user@provider.fr", "password");
        $this->expectException(NotAuthorizedException::class);
        $this->useCase->handle($loginRequest);
    }
}
