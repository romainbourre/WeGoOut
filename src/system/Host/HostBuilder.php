<?php


namespace System\Host
{


    use Closure;
    use JetBrains\PhpStorm\Pure;
    use System\Configuration\Configuration;
    use System\Configuration\IConfigurationBuilder;
    use System\Exceptions\FileConfigurationException;
    use System\Exceptions\IncorrectStartUpClass;
    use System\Configuration\IConfiguration;
    use System\Logging\ILogger;
    use System\Logging\ILoggers;
    use System\Logging\ILoggingBuilder;
    use System\Logging\Loggers;

    class HostBuilder implements IHostBuilder, IConfigurationBuilder, ILoggingBuilder
    {
        private ?string        $startUpClass;
        private IConfiguration  $configuration;
        private ILoggers       $loggers;

        /**
         * HostBuilder constructor.
         */
        #[Pure]
        function __construct()
        {
            $this->configuration = new Configuration();
            $this->loggers = new Loggers();
            $this->startUpClass = null;
        }

        # CONFIGURATION BUILDER

        /**
         * @inheritDoc
         */
        public function addEnvironmentVariables(): IConfigurationBuilder
        {
            $environmentConfigurations = getenv();

            foreach ($environmentConfigurations as $key => $config)
            {
                $composedKeysArray = array_reverse(explode('_', $key));
                $configurationElement = null;
                foreach ($composedKeysArray as $composedKey)
                {
                    if (is_null($configurationElement))
                    {
                        $configurationElement = [$composedKey => $config];
                        continue;
                    }

                    $configurationElement = [$composedKey => $configurationElement];
                }

                $this->configuration->merge($configurationElement);
            }

            return $this;
        }

        /**
         * @inheritDoc
         */
        public function addJsonConfiguration(string $pathToJsonFile, bool $isMandatory = true): IConfigurationBuilder
        {
            $fileContent = @file_get_contents($pathToJsonFile);

            if ($fileContent)
            {
                $fileConfiguration = json_decode($fileContent, true);
                $this->configuration->merge($fileConfiguration);

                return $this;
            }

            if ($isMandatory)
            {
                throw new FileConfigurationException("file '{$pathToJsonFile}' could not be loaded. please ensure that the file exist.");
            }

            return $this;
        }

        # LOGGING BUILDER

        /**
         * @inheritDoc
         */
        public function addLogger(ILogger $logger): ILoggingBuilder
        {
            $this->loggers->addLogger($logger);

            return $this;
        }

        # HOST BUILDER

        /**
         * @inheritDoc
         */
        #[Pure]
        public function build(): IHost
        {
            return new Host($this->configuration, $this->loggers, $this->startUpClass);
        }

        /**
         * @inheritDoc
         */
        public function configuration(Closure $action): IHostBuilder
        {
            $action($this, $this->configuration);

            return $this;
        }

        /**
         * @inheritDoc
         */
        public function configureLogging(Closure $action): IHostBuilder
        {
            $action($this, $this->configuration);

            return $this;
        }

        /**
         * @inheritDoc
         */
        public function useStartUp(string $class): IHostBuilder
        {
            if (($implements = class_implements($class)) && !isset($implements[IStartUp::class]))
            {
                throw new IncorrectStartUpClass();
            }

            $this->startUpClass = $class;
            return $this;
        }
    }
}