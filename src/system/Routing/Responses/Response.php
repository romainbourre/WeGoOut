<?php


namespace System\Routing\Responses
{


    abstract class Response extends \Slim\Psr7\Response
    {
        /**
         * Response constructor.
         */
        public function __construct(mixed $body = null)
        {
            parent::__construct();

            if (!is_null($body))
            {
                if (is_object($body))
                {
                    $this->headers->addHeader('Content-Type', 'application/json');
                    $body = json_encode($body);
                }

                if (!is_string($body))
                {
                    $body = "$body";
                }

                $this->getBody()->write($body);
            }
        }

        /**
         * Redirect to route
         * @param string $route
         * @return Response
         */
        public function withRedirectTo(string $route): Response
        {
            return $this->withHeader('Location', $route);
        }
    }
}