<?php


namespace WebApp
{


    use Exception;
    use System\Configuration\ConfigurationBuilderInterface;
    use System\Configuration\ConfigurationInterface;
    use System\Host\Host;
    use System\Logging\Logger\ConsoleLogger\FileLogger;
    use System\Logging\LoggingBuilderInterface;
    use WebApp\Logging\Logger\SentryLogger;
    use function Sentry\init;

    class Program
    {
        /**
         * @throws Exception
         */
        public static function main(): void
        {
            Host::createDefaultHostBuilder(__DIR__)
                ->configuration(function (ConfigurationBuilderInterface $builder, ConfigurationInterface $configuration) {

                    $environment = $configuration['Environment'];
                    $version = $configuration['Version'];
                    $sentryDsn = $configuration['Sentry:Dsn'];
                    if (!is_null($sentryDsn)) {
                        init(['dsn' => $sentryDsn, 'environment' => $environment, 'release' => "app@$version"]);
                    }

                    date_default_timezone_set('Europe/Paris');

                    define('ROOT', substr(__DIR__, 0, strpos(__DIR__, "/WebApp")));
                    define('SYS', ROOT . "/system");
                    define('APP', ROOT . "/WebApp");
                    define('CONF', $configuration->toArray());

                    session_name("EVENT_PROJECT");
                    session_start();
                })
                ->configureLogging(function (LoggingBuilderInterface $builder, ConfigurationInterface $configuration) {
                    $sentryLogLevel = $configuration['Sentry:Logging:Level'];
                    $builder->addLogger(new SentryLogger($sentryLogLevel ?? SentryLogger::SentryInfo));
                    $builder->addLogger(new FileLogger(ROOT . "/../application.log"));
                })
                ->useStartUp(Startup::class)
                ->build()
                ->run();
        }
    }
}
