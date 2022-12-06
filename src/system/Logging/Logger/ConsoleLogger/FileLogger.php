<?php


namespace System\Logging\Logger\ConsoleLogger
{


    use DateTime;
    use Exception;
    use JetBrains\PhpStorm\Pure;
    use System\Logging\ILogger;

    class FileLogger implements ILogger
    {
        /**
         * @var false|resource resource to write console
         */
        private $resource;

        /**
         * @var int minimum level of log to display
         */
        private int $minimumLevelDisplay;

        /**
         * ConsoleLogger constructor.
         * @param int $minimumLevelToDisplay set minimum ti display log in console
         */
        public function __construct(string $file, string $mode = "a", int $minimumLevelToDisplay = self::LOG_TYPE_INFO)
        {
            $this->minimumLevelDisplay = $minimumLevelToDisplay;
            $this->resource = fopen($file, $mode);
        }

        /**
         * @inheritDoc
         */
        public function logTrace(?string $message)
        {
            $this->log(self::LOG_TYPE_TRACE, $message);
        }

        /**
         * @inheritDoc
         */
        public function logDebug(?string $message)
        {
            $this->log(self::LOG_TYPE_DEBUG, $message);
        }

        /**
         * @inheritDoc
         */
        public function logInfo(?string $message)
        {
            $this->log(self::LOG_TYPE_INFO, $message);
        }

        /**
         * @inheritDoc
         */
        public function logWarning(?string $message)
        {
            $this->log(self::LOG_TYPE_WARNING, $message);
        }

        /**
         * @inheritDoc
         */
        public function logError(?string $message, ?Exception $exception = null)
        {
            $this->log(self::LOG_TYPE_ERROR, $message, $exception);
        }

        /**
         * @inheritDoc
         */
        public function logCritical(?string $message, ?Exception $exception = null)
        {
            $this->log(self::LOG_TYPE_CRITICAL, $message, $exception);
        }

        /**
         * Log message
         * @param int $level importance level of message
         * @param string|null $message message
         * @param ?Exception $exception exception
         */
        private function log(int $level, ?string $message, ?Exception $exception = null): void
        {
            if ($level < $this->minimumLevelDisplay)
            {
                return;
            }

            $now = new DateTime();
            $type = $this->getType($level);

            if (!is_null($exception))
            {
                $message = "$message\n{$this->computeException($exception)}";
            }

            $log = $this->getColorTemplateForLevel($level, "[{$now->format('D M d H:i:s Y')}] [$type] $message");

            fwrite($this->resource, $log);
        }

        /**
         * Get level name of log
         * @param int $level level of log
         * @return string named level
         */
        private function getType(int $level): string
        {
            return match ($level)
            {
                self::LOG_TYPE_TRACE => 'TRACE',
                self::LOG_TYPE_DEBUG => 'DEBUG',
                self::LOG_TYPE_INFO => 'INFORMATION',
                self::LOG_TYPE_WARNING => 'WARNING',
                self::LOG_TYPE_ERROR => 'ERROR',
                self::LOG_TYPE_CRITICAL => 'CRITICAL',
                default => 'UNKNOWN',
            };
        }

        /**
         * Around message with colored template
         * @param int $level level of log
         * @param string $message log message
         * @return string message with colored template
         */
        private function getColorTemplateForLevel(int $level, string $message): string
        {
            return match ($level)
            {
                self::LOG_TYPE_TRACE => "\e[1;37m$message\e[0m\n",
                self::LOG_TYPE_DEBUG, self::LOG_TYPE_INFO => "\e[0;0m$message\e[0m\n",
                self::LOG_TYPE_WARNING => "\e[1;33m$message\e[0m\n",
                self::LOG_TYPE_ERROR => "\e[1;31m$message\e[0m\n",
                self::LOG_TYPE_CRITICAL => "\e[1;37;41m$message\e[0m\n",
                default => "\e[0m$message\e[0m\n",
            };
        }

        /**
         * Get trace of exception
         * @param Exception $exception
         * @return string
         */
        #[Pure]
        private function computeException(Exception $exception): string
        {
            $exceptionName = get_class($exception);
            return "Exception $exceptionName: {$exception->getMessage()}\nat {$exception->getFile()}({$exception->getLine()})\n{$exception->getTraceAsString()}";
        }
    }
}
