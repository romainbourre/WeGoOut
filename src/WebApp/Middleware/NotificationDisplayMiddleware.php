<?php


namespace WebApp\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use WebApp\Authentication\AuthenticationContext;
    use WebApp\Controllers\NotificationsCenterController;

    class NotificationDisplayMiddleware implements MiddlewareInterface
    {
        public function __construct(private readonly AuthenticationContext $authenticationGateway)
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            (new NotificationsCenterController($this->authenticationGateway))->getView();

            return $handler->handle($request);
        }
    }
}