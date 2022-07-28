<?php


namespace App\Middleware
{


    use Domain\Entities\User;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use System\Routing\Responses\RedirectedResponse;
    use System\Routing\Responses\UnauthorizedResponse;

    class AccountNotValidatedGuardMiddleware implements MiddlewareInterface
    {

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            /* @var User $user */
            $user = $_SESSION['USER_DATA'];

            if (!$user->isValidate())
            {
                return $handler->handle($request);
            }

            return RedirectedResponse::to('/');
        }
    }
}