<?php

namespace Tests\Utils\Contexts;

use Business\Entities\User;
use Business\Ports\AuthenticationContextInterface;

class DeterministAuthenticationContext implements AuthenticationContextInterface
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