<?php

namespace WebApp\Middleware;

use Business\Services\EventService\IEventService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use System\Logging\ILogger;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\CreateEventController;
use WebApp\Services\ToasterService\ToasterInterface;

class CreateEventMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly ILogger $logger,
        private readonly IEventService $eventService,
        private readonly AuthenticationContext $authenticationGateway,
        private readonly ToasterInterface $toaster
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        (new CreateEventController(
            $this->logger, $this->eventService, $this->authenticationGateway, $this->toaster
        ))->getView();
        return $handler->handle($request);
    }
}