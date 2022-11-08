<?php

namespace Adapters\TokenProvider;

use Business\Ports\TokenProviderInterface;

class TokenProvider implements TokenProviderInterface
{

    public function getNext(): string
    {
        return md5(microtime());
    }
}