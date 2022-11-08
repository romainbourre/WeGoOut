<?php

namespace Business\Ports;

interface PasswordGeneratorInterface
{
    public function generate(): string;
}