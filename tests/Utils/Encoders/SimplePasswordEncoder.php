<?php

namespace Tests\Utils\Encoders;

use Business\Ports\PasswordEncoderInterface;

class SimplePasswordEncoder implements PasswordEncoderInterface
{

    public function encode(string $password): string
    {
        return md5($password);
    }
}