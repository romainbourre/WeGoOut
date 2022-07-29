<?php

namespace App\Authentication;

use App\Exceptions\NotConnectedUserException;
use Domain\Entities\UserCli;

class AuthenticationContext
{
    private ?UserCli $connectedUser = null;

    public function setConnectedUser(UserCli $user): void {
        $this->connectedUser = $user;
    }

    public function getConnectedUser(): ?UserCli {
        return $this->connectedUser;
    }

    /**
     * @throws NotConnectedUserException
     */
    public function getConnectedUserOrThrow(): UserCli {
        if (is_null($this->connectedUser)) {
            throw new NotConnectedUserException();
        }
        return $this->connectedUser;
    }
}