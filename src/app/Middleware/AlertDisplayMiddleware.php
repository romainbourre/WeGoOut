<?php


namespace App\Middleware
{


    use Domain\Entities\Alert;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    class AlertDisplayMiddleware implements MiddlewareInterface
    {

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $response = $handler->handle($request);

            if ($response->hasHeader('Location'))
            {
                return $response;
            }

            ob_start();
            Alert::autoReadAlerts();
            $content = ob_get_clean();

            $response->getBody()->write($content);
            return $response;
        }
    }
}