<?php


namespace Business\Exceptions
{


    use Exception;

    class UserAlreadyValidatedException extends Exception
    {

        /**
         * UserAlreadyValidatedException constructor.
         */
        public function __construct()
        {
            parent::__construct("user is already validated");
        }
    }
}