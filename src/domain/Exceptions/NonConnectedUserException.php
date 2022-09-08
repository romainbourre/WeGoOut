<?php

namespace Domain\Exceptions;

use Exception;

class NonConnectedUserException extends Exception
{

    public function __construct()
    {
        parent::__construct('non connected user');
    }
}