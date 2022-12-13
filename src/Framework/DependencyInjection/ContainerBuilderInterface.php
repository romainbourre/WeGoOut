<?php

namespace System\DependencyInjection;

interface ContainerBuilderInterface
{
    public function addService(string $name, string|object $service): void;
}
