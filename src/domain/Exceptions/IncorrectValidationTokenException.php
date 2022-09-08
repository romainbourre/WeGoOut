<?php


namespace Domain\Exceptions;


use Exception;

class IncorrectValidationTokenException extends Exception
{

    public function __construct()
    {
        parent::__construct("incorrect token to validate account");
    }
}
