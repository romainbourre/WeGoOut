<?php

namespace Adapters\Md5PasswordEncoder;

use Business\Ports\PasswordEncoderInterface;

class Md5PasswordEncoder implements PasswordEncoderInterface
{

    public function encode(string $password): string
    {
        return md5($password);
    }
}