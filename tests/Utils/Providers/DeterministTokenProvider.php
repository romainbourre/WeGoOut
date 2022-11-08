<?php

namespace Tests\Utils\Providers;

use Business\Ports\TokenProviderInterface;

class DeterministTokenProvider implements TokenProviderInterface
{
    private string|null $token = null;

    public function setNext(string $token): void
    {
        $this->token = $token;
    }

    public function getNext(): string
    {
        if ($this->token == null) {
            return 'a random token';
        }
        return $this->token;
    }
}