<?php


namespace System\Exceptions
{


    use Exception;
    use JetBrains\PhpStorm\Pure;
    use System\IStartUp;

    class IncorrectStartUpClass extends Exception
    {

        /**
         * IncorrectStartUpClass constructor.
         */
        #[Pure]
        public function __construct()
        {
            $class = IStartUp::class;
            parent::__construct("class doesn't implemented $class");
        }
    }
}