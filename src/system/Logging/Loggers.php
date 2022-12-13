<?php


namespace System\Logging
{


    use Closure;
    use Exception;

    class Loggers implements LoggersInterface, LoggerInterface
    {
        /**
         * @var array<LoggerInterface>
         */
        private array $loggers = [];

        /**
         * @inheritDoc
         */
        public function logTrace(string $message)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logTrace($message));
        }

        /**
         * @inheritDoc
         */
        public function logDebug(string $message)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logDebug($message));
        }

        /**
         * @inheritDoc
         */
        public function logInfo(string $message)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logInfo($message));
        }

        /**
         * @inheritDoc
         */
        public function logWarning(string $message)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logWarning($message));
        }

        /**
         * @inheritDoc
         */
        public function logError(string $message, ?Exception $exception = null)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logError($message, $exception));
        }

        /**
         * @inheritDoc
         */
        public function logCritical(string $message, ?Exception $exception = null)
        {
            $this->forEachLoggerDo(fn(LoggerInterface $logger) => $logger->logCritical($message, $exception));
        }

        /**
         * @inheritDoc
         */
        public function addLogger(LoggerInterface $logger): void
        {
            $this->loggers[] = $logger;
        }

        /**
         * Execute action for each logger in list
         * @param Closure $action
         */
        private function forEachLoggerDo(Closure $action): void
        {
            foreach ($this->loggers as $logger)
            {
                $action($logger);
            }
        }
    }
}
