<?php

namespace Domain\Exceptions;

use Domain\Entities\User;
use Throwable;

/**
 * Class UserDeletedException
 * When user have deleted
 * @package App\Exceptions
 */
class UserDeletedException extends \Exception {

    private $user;

    /**
     * UserDeletedException constructor.
     * @param User|null $user
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(User $user = null, string $message = "L'utilisateur a été supprimé", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
    }

    /**
     * Get deleted user
     * @return User user
     */
    public function getUser(): User {
        return $this->user;
    }

}