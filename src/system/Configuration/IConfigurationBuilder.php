<?php


namespace System\Configuration
{


    use System\Exceptions\FileConfigurationException;

    interface IConfigurationBuilder
    {
        /**
         * Add System environment variables to configuration
         * @return $this
         */
        public function addEnvironmentVariables(): self;

        /**
         * Add configuration from JSON file
         * @param string $pathToJsonFile path to JSON file
         * @param bool $isMandatory if file is obligatory, then throw exception if not loaded
         * @return IConfigurationBuilder
         * @throws FileConfigurationException
         */
        public function addJsonConfiguration(string $pathToJsonFile, bool $isMandatory = false): self;
    }
}