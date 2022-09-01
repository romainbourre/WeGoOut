<?php

namespace Tests\Unit\Web\Authentication;

use App\Authentication\AuthenticationContext;
use App\Exceptions\NotConnectedUserException;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;

final class AuthenticationContextTest extends TestCase
{
    private readonly AuthenticationContext $authenticationContext;


    public function __construct()
    {
        parent::__construct();
        $this->authenticationContext = new AuthenticationContext();
    }

    public function testThatSavedUserInContextReturned(): void
    {
        $user = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($user);
        $userOfContext = $this->authenticationContext->getConnectedUser();
        $this->assertEquals($user, $userOfContext);
    }

    public function testThatContextWithNoUserReturnNull(): void
    {
        $userOfContext = $this->authenticationContext->getConnectedUser();
        $this->assertNull($userOfContext);
    }

    public function testThatContextWithNoUserThrowException(): void
    {
        $this->expectException(NotConnectedUserException::class);
        $this->authenticationContext->getConnectedUserOrThrow();
    }
}