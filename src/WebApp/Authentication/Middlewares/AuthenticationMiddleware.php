<?php

namespace WebApp\Authentication\Middlewares;

use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\UserNotExistException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WebApp\Authentication\AuthenticationConstants;
use WebApp\Authentication\AuthenticationContext;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthenticationContext $authenticationGateway)
    {
    }

    /**
     * @throws DatabaseErrorException
     * @throws UserNotExistException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY])) {
            $userId = $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY];
            $connectedUser = User::load($userId);
            $this->authenticationGateway->setConnectedUser($connectedUser);
        }
        return $handler->handle($request);
    }
}