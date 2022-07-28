<?php

namespace System\Host
{


    use Exception;
    use System\Configuration\IConfiguration;
    use System\Configuration\IConfigurationBuilder;
    use System\Logging\ILogger;
    use System\Logging\ILoggingBuilder;
    use System\Logging\Logger\ConsoleLogger\ConsoleLogger;

    class Host implements IHost
    {
        private const SESSION_USER_VARIABLE = "USER_DATA";

        private IConfiguration  $configuration;
        private ILogger        $logger;
        private ?string        $startUpClass;

        /**
         * System constructor.
         * @param IConfiguration $configuration
         * @param ILogger $logger
         * @param string|null $startUpClass
         */
        function __construct(IConfiguration $configuration, ILogger $logger, ?string $startUpClass)
        {
            $this->configuration = $configuration;
            $this->logger = $logger;
            $this->startUpClass = $startUpClass;
        }

        /**
         * @inheritDoc
         */
        public static function createDefaultHostBuilder(string $rootPath): IHostBuilder
        {
            /**
             * @var IHostBuilder $hostBuilder
             */
            $hostBuilder = new HostBuilder();

            $hostBuilder->configuration(function (IConfigurationBuilder $builder, IConfiguration $configuration) use ($rootPath)
            {
                $builder->addJsonConfiguration("$rootPath/app.settings.json");
                $builder->addJsonConfiguration("$rootPath/app.settings.development.json", false);
                $builder->addJsonConfiguration("$rootPath/app.settings.local.json", false);
                $builder->addEnvironmentVariables();
            });

            $hostBuilder->configureLogging(function (ILoggingBuilder $builder, IConfiguration $configuration)
            {
                $levelLabel = $configuration['Logging:Level'];

                $level = match (strtolower($levelLabel))
                {
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
            try
            {

                /**
                 * @var ?IStartUp $startup
                 */
                $startup = !is_null($this->startUpClass) ? new $this->startUpClass($this->configuration, $this->logger) : null;

                if (is_null($startup))
                {
                    return;
                }

                $startup->run();

            }
            catch (Exception $exception)
            {
                $this->logger->logCritical($exception->getMessage(), $exception);
            }
        }

        /**
         * Get user session
         * @return null
         */
        public static function getMe()
        {
            if (isset($_SESSION[self::SESSION_USER_VARIABLE]) && !empty($_SESSION[self::SESSION_USER_VARIABLE])) return $_SESSION[self::SESSION_USER_VARIABLE];
            return null;
        }
    }
}