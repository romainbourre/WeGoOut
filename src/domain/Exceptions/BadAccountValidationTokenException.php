<?php


namespace Domain\Exceptions
{


    use Exception;

    class BadAccountValidationTokenException extends Exception
    {

        /**
         * BadAccountValidationTokenException constructor.
         */
        public function __construct()
        {
            parent::__construct("bad token to validate account");
        }
    }
}