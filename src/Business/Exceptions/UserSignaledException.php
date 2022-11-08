<?php

namespace Business\Exceptions;

use Business\Entities\User;
use Throwable;

/**
 * Class UserSignaledException
 * When user have signaled
 * @package App\Exceptions
 */
class UserSignaledException extends \Exception
{

    private readonly User $user;

    public function __construct(User $user = null, string $message = "L'utilisateur a été invalidé", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    public function getUser(): User {
        return $this->user;
    }
}