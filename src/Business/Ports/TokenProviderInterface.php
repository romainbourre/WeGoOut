<?php

namespace Business\Ports;

interface TokenProviderInterface
{
    public function getNext(): string;
}