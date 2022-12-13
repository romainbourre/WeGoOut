<?php


namespace System\Logging
{


    interface LoggersInterface
    {
        /**
         * Add logger
         * @param LoggerInterface $logger
         */
        public function addLogger(LoggerInterface $logger): void;
    }
}
