<?php


namespace System\Configuration
{


    use ArrayAccess;
    use System\Exceptions\ConfigurationVariableNotFoundException;
    use System\Exceptions\IncorrectConfigurationVariableException;

    interface IConfiguration extends ArrayAccess
    {
        /**
         * Merge configuration array in actual configuration
         * @param array $configuration
         */
        public function merge(array $configuration): void;

        /**
         * Get configuration from key that is required.
         * @param string $key Key of configuration.
         * @return string Value of configuration.
         * @throws ConfigurationVariableNotFoundException
         * @throws IncorrectConfigurationVariableException
         */
        public function getRequired(string $key): string;

        /**
         * Return configuration as array
         * @return array
         */
        public function toArray(): array;
    }
}