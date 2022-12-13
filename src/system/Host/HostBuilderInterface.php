<?php


namespace System\Host
{


    use Closure;
    use System\Configuration\ConfigurationInterface;
    use System\Configuration\IConfigurationBuilder;
    use System\Exceptions\IncorrectStartUpClass;
    use System\Logging\LoggingBuilderInterface;

    interface HostBuilderInterface
    {
        /**
         * Create Host from HostBuilder
         * @return HostInterface
         */
        public function build(): HostInterface;

        /**
         * Set global configuration
         * @param Closure<IConfigurationBuilder, ConfigurationInterface> $action
         * @return HostBuilderInterface
         */
        public function configuration(Closure $action): HostBuilderInterface;

        /**
         * Set configuration about logging
         * @param Closure<LoggingBuilderInterface, ConfigurationInterface> $action
         * @return HostBuilderInterface
         */
        public function configureLogging(Closure $action): HostBuilderInterface;

        /**
         * Use StartUp class
         * @param string $class IStartUp Class
         * @return HostBuilderInterface
         * @throws IncorrectStartUpClass
         */
        public function useStartUp(string $class): HostBuilderInterface;
    }
}
