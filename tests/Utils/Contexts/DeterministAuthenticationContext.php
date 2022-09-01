<?php

namespace Tests\Utils\Contexts;

use Domain\Entities\User;
use Domain\Interfaces\IAuthenticationContext;

class DeterministAuthenticationContext implements IAuthenticationContext
{
    private ?User $connectedUser = null;

    public function getConnectedUser(): ?User
    {
        return $this->connectedUser;
    }

    public function setConnectedUser(User $user): void
    {
        $this->connectedUser = $user;
    }
}