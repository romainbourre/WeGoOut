<?php

namespace WebApp\Extensions;

use Adapters\MySqlDatabase\Repositories\EventCategoryRepository;
use Business\Ports\EventCategoryRepositoryInterface;
use PDO;
use System\Configuration\ConfigurationInterface;
use System\DependencyInjection\ContainerBuilderInterface;
use System\Exceptions\ConfigurationVariableNotFoundException;
use System\Exceptions\IncorrectConfigurationVariableException;

class MySqlExtension
{
    /**
     * @throws IncorrectConfigurationVariableException
     * @throws ConfigurationVariableNotFoundException
     */
    public static function use(ContainerBuilderInterface $services, ConfigurationInterface $configuration): void
    {
        $databaseContext = new PDO(
            $configuration->getRequired('Database:ConnectionString'),
            $configuration->getRequired('Database:User'),
            $configuration->getRequired('Database:Password')
        );
        $services->addService(PDO::class, $databaseContext);
        $services->addService(EventCategoryRepositoryInterface::class, EventCategoryRepository::class);
    }
}
