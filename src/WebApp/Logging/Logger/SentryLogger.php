<?php


namespace WebApp\Logging\Logger
{


    use Exception;
    use Sentry\Severity;
    use System\Logging\LoggerInterface;
    use function Sentry\captureException;
    use function Sentry\captureMessage;

    class SentryLogger implements LoggerInterface
    {
        public const SentryDebug = 'debug';
        public const SentryInfo = 'info';
        public const SentryWarning = 'warning';
        public const SentryError = 'error';
        public const SentryFatal = 'fatal';


        private int $minimumLevel;

        /**
         * SentryLogger constructor.
         */
        public function __construct(string $minimumLevel = self::SentryDebug)
        {
            $this->minimumLevel = match (strtolower($minimumLevel))
            {
                self::SentryInfo => self::LOG_TYPE_INFO,
                self::SentryWarning => self::LOG_TYPE_WARNING,
                self::SentryError => self::LOG_TYPE_ERROR,
                self::SentryFatal => self::LOG_TYPE_CRITICAL,
                default => self::LOG_TYPE_DEBUG
            };
        }

        /**
         * @inheritDoc
         */
        public function logTrace(string $message)
        {
        }

        /**
         * @inheritDoc
         */
        public function logDebug(string $message)
        {
            $this->log($message, self::LOG_TYPE_DEBUG);
        }

        /**
         * @inheritDoc
         */
        public function logInfo(string $message)
        {
            $this->log($message, self::LOG_TYPE_INFO);
        }

        /**
         * @inheritDoc
         */
        public function logWarning(string $message)
        {
            $this->log($message, self::LOG_TYPE_WARNING);
        }

        /**
         * @inheritDoc
         */
        public function logError(string $message, ?Exception $exception = null)
        {
            $this->log($message, self::LOG_TYPE_ERROR, $exception);
        }

        /**
         * @inheritDoc
         */
        public function logCritical(string $message, ?Exception $exception = null)
        {
            $this->log($message, self::LOG_TYPE_CRITICAL, $exception);
        }

        private function log(string $message, int $level, ?Exception $exception = null): void
        {
            if ($level < $this->minimumLevel)
            {
                return;
            }

            $severity = match ($level)
            {
                self::LOG_TYPE_DEBUG => Severity::debug(),
                self::LOG_TYPE_INFO => Severity::info(),
                self::LOG_TYPE_WARNING => Severity::warning(),
                self::LOG_TYPE_ERROR => Severity::error(),
                self::LOG_TYPE_CRITICAL => Severity::fatal(),
                default => null
            };

            captureMessage($message, $severity);

            if (!is_null($exception))
            {
                captureException($exception);
            }
        }
    }
}
