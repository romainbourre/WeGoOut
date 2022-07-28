<?php


namespace App\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use Slim\Psr7\Response;

    class NonAuthenticatedUserGuardMiddleware implements MiddlewareInterface
    {

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            if (isset($_SESSION['USER_DATA']))
            {
                $response = new Response();
                return $response->withAddedHeader('Location', '/');
            }

            return $handler->handle($request);
        }
    }
}