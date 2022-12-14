<?php

namespace WebApp\Middleware;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use System\Logging\LoggerInterface;
use System\Routing\Responses\InternalServerErrorResponse;

class ErrorManagerMiddleware implements MiddlewareInterface
{


    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        }
        catch (Exception $exception) {
            $this->logger->logError($exception->getMessage(), $exception);
            return new InternalServerErrorResponse($exception->getMessage());
        }
    }
}
