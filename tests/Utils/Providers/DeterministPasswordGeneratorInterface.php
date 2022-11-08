<?php

namespace Tests\Utils\Providers;

use Business\Ports\PasswordGeneratorInterface;

class DeterministPasswordGeneratorInterface implements PasswordGeneratorInterface
{
    private string|null $nextPassword;

    public function setNext(string $nextPassword): string
    {
        $this->nextPassword = $nextPassword;
        return $this->nextPassword;
    }

    public function generate(): string
    {
        return $this->nextPassword ?? 'next-password';
    }
}