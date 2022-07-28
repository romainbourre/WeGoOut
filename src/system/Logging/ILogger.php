<?php


namespace System\Logging
{


    use Exception;

    interface ILogger
    {
        public const LOG_TYPE_TRACE = 0;
        public const LOG_TYPE_DEBUG = 1;
        public const LOG_TYPE_INFO = 2;
        public const LOG_TYPE_WARNING = 3;
        public const LOG_TYPE_ERROR = 4;
        public const LOG_TYPE_CRITICAL = 5;

        /**
         * Log trace message
         * @param string $message message
         */
        public function logTrace(string $message);

        /**
         * Log debug message
         * @param string $message message
         */
        public function logDebug(string $message);

        /**
         * Log information message
         * @param string $message message
         */
        public function logInfo(string $message);

        /**
         * Log warning message
         * @param string $message message
         */
        public function logWarning(string $message);

        /**
         * Log error message
         * @param string $message message
         * @param ?Exception $exception exception
         */
        public function logError(string $message, ?Exception $exception = null);

        /**
         * Log critical message
         * @param string $message message
         * @param ?Exception $exception exception
         */
        public function logCritical(string $message, ?Exception $exception = null);
    }
}