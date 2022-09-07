<?php

namespace Infrastructure\TokenProvider;

use Domain\Interfaces\TokenProviderInterface;

class TokenProvider implements TokenProviderInterface
{

    public function getNext(): string
    {
        return md5(microtime());
    }
}