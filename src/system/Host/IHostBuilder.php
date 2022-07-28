<?php


namespace System\Host
{


    use Closure;
    use System\Configuration\IConfiguration;
    use System\Configuration\IConfigurationBuilder;
    use System\Exceptions\IncorrectStartUpClass;
    use System\Logging\ILoggingBuilder;

    interface IHostBuilder
    {
        /**
         * Create Host from HostBuilder
         * @return IHost
         */
        public function build(): IHost;

        /**
         * Set global configuration
         * @param Closure<IConfigurationBuilder, IConfiguration> $action
         * @return IHostBuilder
         */
        public function configuration(Closure $action): IHostBuilder;

        /**
         * Set configuration about logging
         * @param Closure<ILoggingBuilder, IConfiguration> $action
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