<?php

namespace System\DependencyInjection;

use function DI\autowire;

readonly class Container implements ContainerBuilderInterface, ContainerInterface
{
    private \DI\Container $container;

    public function __construct()
    {
        $this->container = new \DI\Container();
    }

    public function addService(string $name, object|string|null $service = null): void
    {
        if (is_null($service)) {
            $this->container->set($name, autowire($name));
        } elseif (is_string($service)) {
            $this->container->set($name, autowire($service));
        } else {
            $this->container->set($name, $service);
        }
    }

    public function get(string $id): object
    {
        return $this->container->get($id);
    }

    public function has(string $id): bool
    {
        return $this->container->has($id);
    }
}
