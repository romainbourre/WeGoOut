<?php

namespace Business\Exceptions;

class TaskNotExistException extends \Exception
{

    public function __construct(int $id)
    {
        $message = "La tâche $id n'existe pas";
        parent::__construct($message);
    }

}