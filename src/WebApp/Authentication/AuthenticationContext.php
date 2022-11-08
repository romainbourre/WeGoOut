<?php

namespace WebApp\Authentication;

use Business\Entities\User;
use Business\Ports\AuthenticationContextInterface;
use WebApp\Exceptions\NotConnectedUserException;

class AuthenticationContext implements AuthenticationContextInterface
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