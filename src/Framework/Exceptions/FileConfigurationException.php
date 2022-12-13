<?php


namespace System\Exceptions
{


    use Exception;
    use JetBrains\PhpStorm\Pure;

    class FileConfigurationException extends Exception
    {
        /**
         * FileConfigurationException constructor.
         * @param string $message
         */
        #[Pure] public function __construct($message = "")
        {
            parent::__construct($message);
        }
    }
}