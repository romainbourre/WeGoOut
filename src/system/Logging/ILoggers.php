<?php


namespace System\Logging
{


    interface ILoggers
    {
        /**
         * Add logger
         * @param ILogger $logger
         */
        public function addLogger(ILogger $logger): void;
    }
}