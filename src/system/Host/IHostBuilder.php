<?php


namespace System\Host
{


    use Closure;
    use System\Configuration\ConfigurationInterface;
    use System\Configuration\IConfigurationBuilder;
    use System\Exceptions\IncorrectStartUpClass;
    use System\Logging\LoggingBuilderInterface;

    interface IHostBuilder
    {
        /**
         * Create Host from HostBuilder
         * @return IHost
         */
        public function build(): IHost;

        /**
         * Set global configuration
         * @param Closure<IConfigurationBuilder, ConfigurationInterface> $action
         * @return IHostBuilder
         */
        public function configuration(Closure $action): IHostBuilder;

        /**
         * Set configuration about logging
         * @param Closure<LoggingBuilderInterface, ConfigurationInterface> $action
         * @return IHostBuilder
         */
        public function configureLogging(Closure $action): IHostBuilder;

        /**
         * Use StartUp class
         * @param string $class IStartUp Class
         * @return IHostBuilder
         * @throws IncorrectStartUpClass
         */
        public function useStartUp(string $class): IHostBuilder;
    }
}
