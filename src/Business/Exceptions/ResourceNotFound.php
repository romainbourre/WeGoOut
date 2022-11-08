<?php


namespace Business\Exceptions
{


    use Exception;

    class ResourceNotFound extends Exception
    {

        /**
         * ResourceNotFound constructor.
         * @param string $message
         */
        public function __construct(string $message)
        {
            parent::__construct($message);
        }
    }
}