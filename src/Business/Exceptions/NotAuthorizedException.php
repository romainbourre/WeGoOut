<?php


namespace Business\Exceptions
{


    use Exception;
    use JetBrains\PhpStorm\Pure;

    class NotAuthorizedException extends Exception
    {

        /**
         * NotAuthorizedException constructor.
         */
        #[Pure]
        public function __construct(string $message)
        {
            parent::__construct($message);
        }
    }
}