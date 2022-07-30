<?php


namespace App\Middleware
{


    use App\Authentication\AuthenticationContext;
    use App\Exceptions\NotConnectedUserException;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use System\Routing\Responses\RedirectedResponse;

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