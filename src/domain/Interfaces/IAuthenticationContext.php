<?php

namespace Domain\Interfaces;

use Domain\Entities\User;

interface IAuthenticationContext
{
    public function setConnectedUser(User $user): void;

    public function getConnectedUser(): ?User;
}