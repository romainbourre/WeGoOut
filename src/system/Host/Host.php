<?php

namespace System\Host;


use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use System\Configuration\IConfiguration;
use System\Configuration\IConfigurationBuilder;
use System\DependencyInjection\Container;
use System\DependencyInjection\ContainerInterface;
use System\Logging\ILogger;
use System\Logging\ILoggingBuilder;
use System\Logging\Logger\ConsoleLogger\ConsoleLogger;

class Host implements IHost
{

    private IConfiguration $configuration;
    private ILogger $logger;
    private ?string $startUpClass;

    public function __construct(IConfiguration $configuration, ILogger $logger, ?string $startUpClass)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->startUpClass = $startUpClass;
    }

    public static function createDefaultHostBuilder(string $rootPath): IHostBuilder
    {
        $hostBuilder = new HostBuilder();

        $hostBuilder->configuration(function (IConfigurationBuilder $builder, IConfiguration $configuration) use ($rootPath) {
            $builder->addJsonConfiguration("$rootPath/app.settings.json");
            $builder->addJsonConfiguration("$rootPath/app.settings.development.json", false);
            $builder->addJsonConfiguration("$rootPath/app.settings.local.json", false);
            $builder->addEnvironmentVariables();
        });

        $hostBuilder->configureLogging(function (ILoggingBuilder $builder, IConfiguration $configuration) {
            $levelLabel = $configuration['Logging:Level'];

            $level = match (strtolower($levelLabel)) {
                "trace" => ILogger::LOG_TYPE_TRACE,
                "debug" => ILogger::LOG_TYPE_DEBUG,
                "information" => ILogger::LOG_TYPE_INFO,
                "warning" => ILogger::LOG_TYPE_WARNING,
                "error" => ILogger::LOG_TYPE_ERROR,
                "critical" => ILogger::LOG_TYPE_CRITICAL,
                default => null
            };

            $builder->addLogger(new ConsoleLogger($level));
        });

        return $hostBuilder;
    }

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        try {
            $container = new Container();
            $container->addService(IConfiguration::class, $this->configuration);
            $container->addService(ILogger::class, $this->logger);
            $container->addService(ContainerInterface::class, $container);

            /**
             * @var IStartUp $startup
             */
            $startup = $container->get($this->startUpClass);
            $startup->configure($this->configuration, $container);

            try {
                $startup->run($this->configuration, $container);
            } catch (Exception $e) {
                $this->logger->logCritical("application error: {$e->getMessage()}", $e);
            }
        } catch (NotFoundExceptionInterface|ContainerExceptionInterface $e) {
            $this->logger->logCritical("starting error: unable to resolve startup class: {$e->getMessage()}", $e);
        } catch (Exception $e) {
            $this->logger->logCritical("starting error: {$e->getMessage()}", $e);
        }
    }
}
