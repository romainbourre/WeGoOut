<?php

namespace Business\Exceptions;

use Exception;

class IncorrectDateFormatException extends Exception
{

    public function __construct(string $unrecognizedFormat)
    {
        parent::__construct("incorrect format '$unrecognizedFormat' to format french day");
    }
}