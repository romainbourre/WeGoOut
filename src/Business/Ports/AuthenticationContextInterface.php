<?php

namespace Business\Ports;

use Business\Entities\User;

interface AuthenticationContextInterface
{
    public function setConnectedUser(User $user): void;

    public function getConnectedUser(): ?User;
}