<?php


namespace WebApp {


    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\NotFoundExceptionInterface;
    use Slim\Exception\HttpNotFoundException;
    use System\Configuration\ConfigurationInterface;
    use System\DependencyInjection\ContainerBuilderInterface;
    use System\DependencyInjection\ContainerInterface;
    use System\Exceptions\ConfigurationVariableNotFoundException;
    use System\Exceptions\IncorrectConfigurationVariableException;
    use System\Host\StartUpInterface;
    use System\Logging\LoggerInterface;
    use WebApp\Extensions\BusinessExtension;
    use WebApp\Extensions\ControllersExtension;
    use WebApp\Extensions\MiddlewaresExtension;
    use WebApp\Extensions\MySqlExtension;
    use WebApp\Extensions\UseCasesExtension;
    use WebApp\Routing\SlimFrameworkRouter;

    class Startup implements StartUpInterface
    {
        private LoggerInterface $logger;

        public function __construct(LoggerInterface $logger)
        {
            $this->logger = $logger;
        }

        /**
         * @throws IncorrectConfigurationVariableException
         * @throws ConfigurationVariableNotFoundException
         */
        public function configure(ConfigurationInterface $configuration, ContainerBuilderInterface $services): void
        {
            MySqlExtension::use($services, $configuration);
            ControllersExtension::use($services);
            BusinessExtension::use($services);
            UseCasesExtension::use($services);
            MiddlewaresExtension::use($services);
        }

        /**
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         */
        public function run(ConfigurationInterface $configuration, ContainerInterface $servicesProvider): void
        {
            try {
                $this->logger->logTrace("Host running...");
                $slimFrameworkRouter = new SlimFrameworkRouter($servicesProvider, $configuration);
                $slimFrameworkRouter->configure()->run();
            } catch (HttpNotFoundException $e) {
                $uri = $e->getRequest()->getUri();
                $this->logger->logWarning("not found route $uri");
                header('Location: /');
            } finally {
                $this->logger->logTrace("Host stopped");
            }
        }
    }
}
