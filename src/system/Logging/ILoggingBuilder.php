<?php


namespace System\Logging
{


    interface ILoggingBuilder
    {
        /**
         * Add logger
         * @param ILogger $logger
         * @return ILoggingBuilder
         */
        public function addLogger(ILogger $logger): ILoggingBuilder;
    }
}