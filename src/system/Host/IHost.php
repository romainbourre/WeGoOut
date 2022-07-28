<?php


namespace System\Host
{


    use Exception;

    interface IHost
    {
        /**
         * Run Host
         */
        public function run(): void;

        /**
         * Create Default Host builder
         * Set default configuration file 'app.settings.json' and  non obligatory 'app.settings.local.json'
         * Add default console logger
         * @param string $rootPath path where program started
         * @return IHostBuilder
         * @throws Exception
         */
        public static function createDefaultHostBuilder(string $rootPath): IHostBuilder;
    }
}