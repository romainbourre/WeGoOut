<?php

namespace WebApp\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use WebApp\Controllers\CreateEventController;

readonly class CreateEventMiddleware implements MiddlewareInterface
{
    public function __construct(private CreateEventController $createEventController)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->createEventController->getView();
        return $handler->handle($request);
    }
}
