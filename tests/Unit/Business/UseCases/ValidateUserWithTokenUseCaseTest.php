<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryUserRepositoryInterface;
use Business\Exceptions\IncorrectValidationTokenException;
use Business\Exceptions\NonConnectedUserException;
use Business\Exceptions\UserAlreadyValidatedException;
use Business\UseCases\ValidateUserAccount\ValidateUserAccountRequest;
use Business\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Contexts\DeterministAuthenticationContext;

class ValidateUserWithTokenUseCaseTest extends TestCase
{
    private readonly DeterministAuthenticationContext $authenticationContext;
    private readonly InMemoryUserRepositoryInterface  $userRepository;
    private readonly ValidateUserAccountUseCase       $useCase;

    public function testThat_Given_NonValidatedSavedUser_When_ValidateUser_Then_SetUserAsValidated()
    {
        $userToken = 'avalidationtoken';
        $user = UserBuilder::given()->withValidationToken($userToken)->create();
        $this->userRepository->addUserWithPassword($user, 'password');
        $this->authenticationContext->setConnectedUser($user);
        $request = new ValidateUserAccountRequest($userToken);
        $this->useCase->handle($request);
        $this->assertNull($this->userRepository->users->first()->validationToken);
    }

    public function testThat_Given_NonValidatedSavedUser_When_ValidateUserWithBadToken_Then_PreventError()
    {
        $user = UserBuilder::given()->withValidationToken('avalidationtoken')->create();
        $this->userRepository->addUserWithPassword($user, 'password');
        $this->authenticationContext->setConnectedUser($user);
        $request = new ValidateUserAccountRequest('otherValidationToken');
        $this->expectException(IncorrectValidationTokenException::class);
        $this->useCase->handle($request);
    }

    public function testThat_Given_AlreadyValidatedSavedUser_When_ValidateUser_Then_PreventError()
    {
        $user = UserBuilder::given()->withValidationToken(null)->create();
        $this->userRepository->addUserWithPassword($user, 'password');
        $this->authenticationContext->setConnectedUser($user);
        $request = new ValidateUserAccountRequest('validationToken');
        $this->expectException(UserAlreadyValidatedException::class);
        $this->useCase->handle($request);
    }

    public function testThat_Given_NonSavedUser_When_ValidateUser_Then_PreventError()
    {
        $request = new ValidateUserAccountRequest('validationToken');
        $this->expectException(NonConnectedUserException::class);
        $this->useCase->handle($request);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticationContext = new DeterministAuthenticationContext();
        $this->userRepository = new InMemoryUserRepositoryInterface();
        $this->useCase = new ValidateUserAccountUseCase($this->authenticationContext, $this->userRepository);
    }
}