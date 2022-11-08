<?php

namespace Business\Exceptions;

use Business\Entities\User;
use Throwable;

/**
 * Class UserDeletedException
 * When user have deleted
 * @package App\Exceptions
 */
class UserDeletedException extends \Exception
{

    private readonly User $user;

    public function __construct(User $user = null, string $message = "L'utilisateur a été supprimé", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    public function getUser(): User {
        return $this->user;
    }

}