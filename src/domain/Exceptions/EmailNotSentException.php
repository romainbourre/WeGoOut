<?php


namespace Domain\Exceptions
{


    use Exception;

    class EmailNotSentException extends Exception
    {
        public function __construct()
        {
            parent::__construct("the email could not be sent");
        }
    }
}