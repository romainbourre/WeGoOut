<?php


namespace System\Host;


use Closure;
use JetBrains\PhpStorm\Pure;
use System\Configuration\Configuration;
use System\Configuration\ConfigurationBuilderInterface;
use System\Configuration\ConfigurationInterface;
use System\Exceptions\FileConfigurationException;
use System\Exceptions\IncorrectStartUpClass;
use System\Logging\LoggerInterface;
use System\Logging\Loggers;
use System\Logging\LoggersInterface;
use System\Logging\LoggingBuilderInterface;

class HostBuilder implements HostBuilderInterface, ConfigurationBuilderInterface, LoggingBuilderInterface
{
    private ?string $startUpClass;
    private ConfigurationInterface $configuration;
    private LoggersInterface $loggers;

    /**
     * HostBuilder constructor.
     */
    #[Pure]
    public function __construct()
    {
        $this->configuration = new Configuration();
        $this->loggers = new Loggers();
        $this->startUpClass = null;
    }

    # CONFIGURATION BUILDER

    /**
     * @inheritDoc
     */
    public function addEnvironmentVariables(): ConfigurationBuilderInterface
    {
        $environmentConfigurations = getenv();
        foreach ($environmentConfigurations as $key => $config) {
            $composedKeysArray = array_reverse(explode('_', $key));
            $configurationElement = null;
            foreach ($composedKeysArray as $composedKey) {
                if (is_null($configurationElement)) {
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
    public function addJsonConfiguration(string $pathToJsonFile, bool $isMandatory = true): ConfigurationBuilderInterface
    {
        $fileContent = @file_get_contents($pathToJsonFile);
        if ($fileContent) {
            $fileConfiguration = json_decode($fileContent, true);
            $this->configuration->merge($fileConfiguration);

            return $this;
        }
        if ($isMandatory) {
            throw new FileConfigurationException("file '{$pathToJsonFile}' could not be loaded. please ensure that the file exist.");
        }
        return $this;
    }

    # LOGGING BUILDER

    /**
     * @inheritDoc
     */
    public function addLogger(LoggerInterface $logger): LoggingBuilderInterface
    {
        $this->loggers->addLogger($logger);
        return $this;
    }

    # HOST BUILDER

    /**
     * @inheritDoc
     */
    #[Pure]
    public function build(): HostInterface
    {
        return new Host($this->configuration, $this->loggers, $this->startUpClass);
    }

    /**
     * @inheritDoc
     */
    public function configuration(Closure $action): HostBuilderInterface
    {
        $action($this, $this->configuration);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function configureLogging(Closure $action): HostBuilderInterface
    {
        $action($this, $this->configuration);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function useStartUp(string $class): HostBuilderInterface
    {
        if (($implements = class_implements($class)) && !isset($implements[StartUpInterface::class])) {
            throw new IncorrectStartUpClass();
        }
        $this->startUpClass = $class;
        return $this;
    }
}
