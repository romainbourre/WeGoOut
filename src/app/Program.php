<?php


namespace App
{


    use App\Logging\Logger\SentryLogger;
    use Exception;
    use System\Configuration\IConfiguration;
    use System\Configuration\IConfigurationBuilder;
    use System\Host\Host;
    use System\Logging\ILoggingBuilder;
    use System\Logging\Logger\ConsoleLogger\FileLogger;

    use function Sentry\init;

    class Program
    {
        /**
         * @throws Exception
         */
        public static function main(): void
        {
            Host::createDefaultHostBuilder(__DIR__)
                ->configuration(function (IConfigurationBuilder $builder, IConfiguration $configuration) {

                    $environment = $configuration['Environment'];
                    $version = $configuration['Version'];
                    $sentryDsn = $configuration['Sentry:Dsn'];
                    if (!is_null($sentryDsn)) {
                        init(['dsn' => $sentryDsn, 'environment' => $environment, 'release' => "app@$version"]);
                    }

                    date_default_timezone_set('Europe/Paris');

                    define('ROOT', substr(__DIR__, 0, strpos(__DIR__, "/app")));
                    define('SYS', ROOT . "/system");
                    define('APP', ROOT . "/app");
                    define('CONF', $configuration->toArray());

                    session_name("EVENT_PROJECT");
                    session_start();
                })
                ->configureLogging(function(ILoggingBuilder $builder, IConfiguration $configuration)
                {
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