<?php

namespace Domain\Exceptions;

use Domain\Entities\User;
use Throwable;

/**
 * Class UserSignaledException
 * When user have signaled
 * @package App\Exceptions
 */
class UserSignaledException extends \Exception {

    private $user;

    public function __construct(User $user = null, string $message = "L'utilisateur a été invalidé", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    /**
     * Signaled user
     * @return User
     */
    public function getUser(): User {
        return $this->user;
    }

}