<?php

namespace Domain\Interfaces;

interface PasswordEncoderInterface
{
    public function encode(string $password): string;
}