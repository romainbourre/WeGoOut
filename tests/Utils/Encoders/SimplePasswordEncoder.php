<?php

namespace Tests\Utils\Encoders;

use Domain\Interfaces\PasswordEncoderInterface;

class SimplePasswordEncoder implements PasswordEncoderInterface
{

    public function encode(string $password): string
    {
        return md5($password);
    }
}