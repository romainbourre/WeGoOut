<?php

namespace Domain\Exceptions;

use Throwable;

/**
 * Class UserIncorrectPasswordException
 * When a password of user is incorrect
 * @package App\Exceptions
 */
class UserIncorrectPasswordException extends \Exception {

    /**
     * UserIncorrectPasswordException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "Le mot de passe de l'utilisateur est incorrect", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}