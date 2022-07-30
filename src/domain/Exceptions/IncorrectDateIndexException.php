<?php

namespace Domain\Exceptions;

use Exception;

class IncorrectDateIndexException extends Exception
{

    public function __construct(string $unrecognizedDayOfWeek)
    {
        parent::__construct("unrecognized day of week '$unrecognizedDayOfWeek'");
    }
}