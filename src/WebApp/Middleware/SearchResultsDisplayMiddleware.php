<?php


namespace WebApp\Middleware
{


    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Http\Server\MiddlewareInterface;
    use Psr\Http\Server\RequestHandlerInterface;
    use System\Logging\ILogger;
    use WebApp\Authentication\AuthenticationContext;
    use WebApp\Controllers\ResearchController;

    class SearchResultsDisplayMiddleware implements MiddlewareInterface
    {


        public function __construct(
            private readonly AuthenticationContext $authenticationContext,
            private readonly ILogger $logger
        ) {
        }

        public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
        {
            (new ResearchController($this->authenticationContext, $this->logger))->getView();

            return $handler->handle($request);
        }
    }
}