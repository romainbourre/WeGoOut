<?php


namespace System\Logging
{


    interface LoggingBuilderInterface
    {
        /**
         * Add logger
         * @param LoggerInterface $logger
         * @return LoggingBuilderInterface
         */
        public function addLogger(LoggerInterface $logger): LoggingBuilderInterface;
    }
}
