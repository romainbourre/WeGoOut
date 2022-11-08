<?php


namespace WebApp\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use System\Routing\Responses\RedirectedResponse;
    use WebApp\Authentication\AuthenticationContext;
    use WebApp\Exceptions\NotConnectedUserException;

    class AccountValidatedGuardMiddleware implements MiddlewareInterface
    {


        public function __construct(private readonly AuthenticationContext $authenticationGateway)
        {
        }

        /**
         * @throws NotConnectedUserException
         */
        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            if ($connectedUser->isValidate()) {
                return $handler->handle($request);
            }
            return RedirectedResponse::to('/validation');
        }
    }
}