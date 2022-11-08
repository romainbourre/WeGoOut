<?php

namespace Business\Ports;

interface PasswordEncoderInterface
{
    public function encode(string $password): string;
}