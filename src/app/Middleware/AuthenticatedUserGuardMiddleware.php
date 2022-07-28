<?php


namespace App\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use Slim\Psr7\Response;

    class AuthenticatedUserGuardMiddleware implements MiddlewareInterface
    {

        /**
         * MyMiddleware constructor.
         */
        public function __construct()
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            if (!isset($_SESSION['USER_DATA']))
            {
                $response = new Response();

                $rep = $response->withHeader('Location', '/login');
                return $rep;
            }

            return $handler->handle($request);
        }
    }
}