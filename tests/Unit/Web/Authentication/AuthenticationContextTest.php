<?php

namespace Tests\Unit\Web\Authentication;

use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\UserBuilder;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Exceptions\NotConnectedUserException;

final class AuthenticationContextTest extends TestCase
{
    private readonly AuthenticationContextInterface $authenticationContext;


    public function __construct()
    {
        parent::__construct();
        $this->authenticationContext = new AuthenticationContext();
    }

    /**
     * @throws ValidationException
     */
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
