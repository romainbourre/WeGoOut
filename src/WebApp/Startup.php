<?php


namespace WebApp
{


    use Exception;
    use Slim\Exception\HttpNotFoundException;
    use Slim\Factory\AppFactory;
    use System\Configuration\Configuration;
    use System\Configuration\IConfiguration;
    use System\Host\IStartUp;
    use System\Logging\ILogger;
    use WebApp\Routing\Router;

    class Startup implements IStartUp
    {
        private ILogger       $logger;
        private Configuration $configuration;

        /**
         * Startup constructor.
         * @param IConfiguration $configuration
         * @param ILogger $logger
         */
        public function __construct(IConfiguration $configuration, ILogger $logger)
        {
            $this->configuration = $configuration;
            $this->logger = $logger;
        }

        /**
         * @throws Exception
         */
        public function run(): void
        {
            try
            {
                $this->logger->logInfo("Host running...");
                $app = AppFactory::create();

                $app->addBodyParsingMiddleware();
                new Router($this->logger, $app, $this->configuration);

                $app->run();
            }
            catch (HttpNotFoundException $e)
            {
                $uri = $e->getRequest()->getUri();
                $this->logger->logWarning("not found route $uri");
                header('Location: /');
            }
            finally
            {
                $this->logger->logInfo("Host stopped");
            }
        }
    }
}