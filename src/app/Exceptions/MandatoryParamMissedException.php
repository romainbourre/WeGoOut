<?php

namespace App\Exceptions;

use Exception;

class MandatoryParamMissedException extends Exception
{

    public function __construct(string $valueName)
    {
        parent::__construct("$valueName value missed from params of request");
    }
}