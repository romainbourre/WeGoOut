<?php

namespace WebApp\Extensions;

use PDO;
use System\Configuration\IConfiguration;
use System\DependencyInjection\ContainerBuilderInterface;
use System\Exceptions\ConfigurationVariableNotFoundException;
use System\Exceptions\IncorrectConfigurationVariableException;

class MySqlExtension
{
    /**
     * @throws IncorrectConfigurationVariableException
     * @throws ConfigurationVariableNotFoundException
     */
    public static function use(ContainerBuilderInterface $services, IConfiguration $configuration): void
    {
        $databaseContext = new PDO(
            $configuration->getRequired('Database:ConnectionString'),
            $configuration->getRequired('Database:User'),
            $configuration->getRequired('Database:Password')
        );
        $services->addService(PDO::class, $databaseContext);
    }
}