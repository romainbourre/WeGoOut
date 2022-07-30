<?php

namespace App\Authentication\Middlewares;

use App\Authentication\AuthenticationConstants;
use App\Authentication\AuthenticationContext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthenticationContext $authenticationGateway)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY])) {
            $connectedUser = $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY];
            $this->authenticationGateway->setConnectedUser($connectedUser);
        }
        return $handler->handle($request);
    }
}