<?php


namespace App\Middleware
{


    use App\Authentication\AuthenticationContext;
    use App\Controllers\ResearchController;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;

    class SearchResultsDisplayMiddleware implements MiddlewareInterface
    {


        public function __construct(private readonly AuthenticationContext $authenticationContext)
        {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            (new ResearchController($this->authenticationContext))->getView();

            return $handler->handle($request);
        }
    }
}