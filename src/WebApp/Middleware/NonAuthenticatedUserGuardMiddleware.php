<?php


namespace WebApp\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use System\Routing\Responses\RedirectedResponse;
    use WebApp\Authentication\AuthenticationContext;

    class NonAuthenticatedUserGuardMiddleware implements MiddlewareInterface
    {


        public function __construct(private readonly AuthenticationContext $authenticationGateway)
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $connectedUser = $this->authenticationGateway->getConnectedUser();
            if (!is_null($connectedUser))
            {
                return RedirectedResponse::to('/');
            }
            return $handler->handle($request);
        }
    }
}