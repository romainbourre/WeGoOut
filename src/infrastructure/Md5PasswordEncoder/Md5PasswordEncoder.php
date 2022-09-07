<?php

namespace Infrastructure\Md5PasswordEncoder;

use Domain\Interfaces\PasswordEncoderInterface;

class Md5PasswordEncoder implements PasswordEncoderInterface
{

    public function encode(string $password): string
    {
        return md5($password);
    }
}