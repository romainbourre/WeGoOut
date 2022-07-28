<?php


namespace System\Exceptions
{


    use Exception;
    use JetBrains\PhpStorm\Pure;

    class ConfigurationVariableNotFoundException extends Exception
    {

        /**
         * ConfigurationVariableNotFoundException constructor.
         */
        #[Pure]
        public function __construct(string $key)
        {
            parent::__construct("variable with key '$key' not found");
        }
    }
}