<?php

namespace Business\Exceptions;

use Throwable;

/**
 * Class UserNotExistException
 * When user no exist
 * @package App\Exceptions
 */
class UserNotExistException extends \Exception
{

    /**
     * UserNotExistException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "L'utilisateur n'existe pas", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}