<?php

namespace Domain\Interfaces;

interface TokenProviderInterface
{
    public function getNext(): string;
}