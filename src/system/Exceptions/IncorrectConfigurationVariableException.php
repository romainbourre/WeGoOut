<?php


namespace System\Exceptions
{


    use Exception;
    use JetBrains\PhpStorm\Pure;

    class IncorrectConfigurationVariableException extends Exception
    {

        /**
         * IncorrectConfigurationVariableException constructor.
         */
        #[Pure]
        public function __construct(string $key)
        {
            parent::__construct("the key $key is not final endpoint.");
        }
    }
}