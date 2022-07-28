<?php


namespace System\Logging\Logger\ConsoleLogger
{


    class ConsoleLogger extends FileLogger {

        public function __construct()
        {
            parent::__construct("php://stdout", "r+");
        }
    }
}