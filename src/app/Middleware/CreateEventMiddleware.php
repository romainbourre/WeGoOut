<?php

namespace App\Middleware;

use App\Authentication\AuthenticationContext;
use App\Controllers\CreateEventController;
use Domain\Services\EventService\IEventService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use System\Logging\ILogger;

class CreateEventMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ILogger $logger,
        private readonly IEventService $eventService,
        private readonly AuthenticationContext $authenticationGateway
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        (new CreateEventController($this->logger, $this->eventService, $this->authenticationGateway))->getView();
        return $handler->handle($request);
    }
}