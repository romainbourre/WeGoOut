<?php

namespace System\Host;


use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use System\Configuration\ConfigurationInterface;
use System\Configuration\IConfigurationBuilder;
use System\DependencyInjection\Container;
use System\DependencyInjection\ContainerInterface;
use System\Logging\Logger\ConsoleLogger\ConsoleLogger;
use System\Logging\LoggerInterface;
use System\Logging\LoggingBuilderInterface;

class Host implements HostInterface
{

    private ConfigurationInterface $configuration;
    private LoggerInterface $logger;
    private ?string $startUpClass;

    public function __construct(ConfigurationInterface $configuration, LoggerInterface $logger, ?string $startUpClass)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
        $this->startUpClass = $startUpClass;
    }

    public static function createDefaultHostBuilder(string $rootPath): HostBuilderInterface
    {
        $hostBuilder = new HostBuilder();

        $hostBuilder->configuration(function (IConfigurationBuilder $builder, ConfigurationInterface $configuration) use ($rootPath) {
            $builder->addJsonConfiguration("$rootPath/app.settings.json");
            $builder->addJsonConfiguration("$rootPath/app.settings.development.json", false);
            $builder->addJsonConfiguration("$rootPath/app.settings.local.json", false);
            $builder->addEnvironmentVariables();
        });

        $hostBuilder->configureLogging(function (LoggingBuilderInterface $builder, ConfigurationInterface $configuration) {
            $levelLabel = $configuration['Logging:Level'];

            $level = match (strtolower($levelLabel)) {
                "trace" => LoggerInterface::LOG_TYPE_TRACE,
                "debug" => LoggerInterface::LOG_TYPE_DEBUG,
                "information" => LoggerInterface::LOG_TYPE_INFO,
                "warning" => LoggerInterface::LOG_TYPE_WARNING,
                "error" => LoggerInterface::LOG_TYPE_ERROR,
                "critical" => LoggerInterface::LOG_TYPE_CRITICAL,
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
            $container->addService(ConfigurationInterface::class, $this->configuration);
            $container->addService(LoggerInterface::class, $this->logger);
            $container->addService(ContainerInterface::class, $container);

            /**
             * @var StartUpInterface $startup
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
