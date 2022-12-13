<?php


namespace System\Host;


use System\Configuration\ConfigurationInterface;
use System\DependencyInjection\ContainerBuilderInterface;
use System\DependencyInjection\ContainerInterface;

interface IStartUp
{
    public function configure(ConfigurationInterface $configuration, ContainerBuilderInterface $services): void;

    public function run(ConfigurationInterface $configuration, ContainerInterface $servicesProvider): void;
}
