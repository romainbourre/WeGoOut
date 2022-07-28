<?php


namespace App\Middleware
{


    use App\Controllers\ResearchController;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    class SearchResultsDisplayMiddleware implements MiddlewareInterface
    {

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            (new ResearchController())->getView();

            return $handler->handle($request);
        }
    }
}