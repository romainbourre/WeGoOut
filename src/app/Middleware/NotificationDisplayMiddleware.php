<?php


namespace App\Middleware
{


    use App\Controllers\NotificationsCenterController;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    class NotificationDisplayMiddleware implements MiddlewareInterface
    {


        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            (new NotificationsCenterController())->getView();

            return $handler->handle($request);
        }
    }
}