<?php


namespace System\Logging\Logger\ConsoleLogger
{


    class ConsoleLogger extends FileLogger {

        public function __construct(string $level)
        {
            parent::__construct("php://stdout", "r+", $level);
        }
    }
}
