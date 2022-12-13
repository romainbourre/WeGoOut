<?php


namespace System\Configuration
{


    use System\Exceptions\ConfigurationVariableNotFoundException;
    use System\Exceptions\IncorrectConfigurationVariableException;

    class Configuration implements ConfigurationInterface
    {
        /**
         * @var array
         */
        private array $configuration = [];

        /**
         * @inheritDoc
         */
        public function merge(array $configuration): void
        {
            $this->array_overwrite($this->configuration, $configuration);
        }

        /**
         * Merge array and overwrite value if exist
         * @param $original
         * @param $overwrite
         */
        private function array_overwrite(&$original, $overwrite)
        {
            if (!is_array($overwrite)) {
                return;
            }
            if (!is_array($original)) {
                $original = $overwrite;
            }

            foreach($overwrite as $key => $value) {
                if (array_key_exists($key, $original) && is_array($value)) {
                    $this->array_overwrite($original[$key], $value);
                } else {
                    $original[$key] = $value;
                }
            }
        }

        /**
         * @inheritDoc
         */
        public function offsetExists($offset): bool
        {
            return isset($this->configuration[$offset]);
        }

        /**
         * @inheritDoc
         * @throws IncorrectConfigurationVariableException
         */
        public function offsetGet($offset): string|null
        {
            $path = explode(':', $offset);
            $config = $this->configuration;
            foreach ($path as $p)
            {
                if (!isset($config[$p]))
                {
                    return null;
                }

                $config = $config[$p];
            }

            if (is_array($config))
            {
                throw new IncorrectConfigurationVariableException($offset);
            }

            return $config;
        }

        /**
         * @inheritDoc
         */
        public function getRequired(string $key): string {
            $value = $this->offsetGet($key);
            if (is_null($value)) {
                throw new ConfigurationVariableNotFoundException($key);
            }
            return $value;
        }

        /**
         * @inheritDoc
         */
        public function offsetSet($offset, $value): void
        {
            $this->configuration[$offset] = $value;
        }

        /**
         * @inheritDoc
         */
        public function offsetUnset($offset): void
        {
            unset($this->configuration[$offset]);
        }

        /**
         * @inheritDoc
         */
        public function toArray(): array
        {
            return $this->configuration;
        }
    }
}
