<?php


namespace System\Host;


use System\Configuration\IConfiguration;
use System\DependencyInjection\ContainerBuilderInterface;
use System\DependencyInjection\ContainerInterface;

interface IStartUp
{
    public function configure(IConfiguration $configuration, ContainerBuilderInterface $services): void;

    public function run(IConfiguration $configuration, ContainerInterface $servicesProvider): void;
}
