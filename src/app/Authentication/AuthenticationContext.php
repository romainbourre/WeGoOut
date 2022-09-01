<?php

namespace App\Authentication;

use App\Exceptions\NotConnectedUserException;
use Domain\Entities\User;
use Domain\Interfaces\IAuthenticationContext;

class AuthenticationContext implements IAuthenticationContext
{
    private ?User $connectedUser = null;

    public function setConnectedUser(User $user): void
    {
        $this->connectedUser = $user;
    }

    public function getConnectedUser(): ?User
    {
        return $this->connectedUser;
    }

    /**
     * @throws NotConnectedUserException
     */
    public function getConnectedUserOrThrow(): User
    {
        if (is_null($this->connectedUser)) {
            throw new NotConnectedUserException();
        }
        return $this->connectedUser;
    }
}