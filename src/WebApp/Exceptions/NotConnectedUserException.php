<?php


namespace WebApp\Exceptions
{


    use Exception;

    class NotConnectedUserException extends Exception
    {

        /**
         * NotConnectedUserException constructor.
         */
        public function __construct()
        {
            parent::__construct();
        }
    }
}