<?php


namespace Business\Exceptions
{


    use Exception;

    class UserAlreadyExistException extends Exception
    {

        /**
         * UserAlreadyExistException constructor.
         */
        public function __construct()
        {
            parent::__construct("user already exist");
        }
    }
}