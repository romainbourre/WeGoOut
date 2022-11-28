<?php


namespace WebApp\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use WebApp\Services\ToasterService\ToasterRepositoryInterface;

    class AlertDisplayMiddleware implements MiddlewareInterface
    {
        public function __construct(private readonly ToasterRepositoryInterface $toasterService)
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            $response = $handler->handle($request);

            if ($response->hasHeader('Location')) {
                return $response;
            }

            ob_start();
            $toasts = $this->toasterService->getToasts();
            foreach ($toasts as $toast) {
                echo $toast;
            }
            $content = ob_get_clean();

            $response->getBody()->write($content);
            return $response;
        }
    }
}