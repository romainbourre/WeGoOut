<?php

require_once '../../../../../vendor/autoload.php';

use Adapters\MySqlDatabase\Repositories\EventRepository;
use Adapters\SendGrid\SendGridAdapter;
use Adapters\TwigRenderer\TwigRendererAdapter;
use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\UserDeletedException;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\UserSignaledException;
use Business\Services\EventService\EventService;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use System\Configuration\ConfigurationBuilderInterface;
use System\Configuration\ConfigurationInterface;
use System\DependencyInjection\ContainerBuilderInterface;
use System\DependencyInjection\ContainerInterface;
use System\Exceptions\ConfigurationVariableNotFoundException;
use System\Exceptions\IncorrectConfigurationVariableException;
use System\Host\Host;
use System\Host\StartUpInterface;
use System\Logging\Logger\ConsoleLogger\FileLogger;
use System\Logging\LoggerInterface;
use System\Logging\LoggingBuilderInterface;
use System\Routing\Responses\OkResponse;
use WebApp\Authentication\AuthenticationConstants;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\NotificationsCenterController;
use WebApp\Controllers\OneEventController;
use WebApp\Controllers\ProfileController;
use WebApp\Controllers\ResearchController;
use WebApp\Librairies\Emitter;
use WebApp\Logging\Logger\SentryLogger;
use function Sentry\init;

error_reporting(E_ALL ^ E_DEPRECATED);


class SwitchStartup implements StartUpInterface
{
    private ConfigurationInterface $configuration;
    private LoggerInterface $logger;

    public function __construct(ConfigurationInterface $configuration, LoggerInterface $logger)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @throws IncorrectConfigurationVariableException
     * @throws UserDeletedException
     * @throws UserNotExistException
     * @throws ConfigurationVariableNotFoundException
     * @throws DatabaseErrorException
     * @throws UserSignaledException
     */
    public function run(ConfigurationInterface $configuration, ContainerInterface $servicesProvider): void
    {
        $connectionString = $this->configuration->getRequired('Database:ConnectionString');
        $databaseContext = new PDO(
            $connectionString,
            $this->configuration->getRequired('Database:User'),
            $this->configuration->getRequired('Database:Password')
        );
        $eventRepository = new EventRepository($databaseContext);
        $authenticationGateway = $this->loadUserInAuthenticationGateway();
        $eventService = new EventService($authenticationGateway, $eventRepository, Emitter::getInstance());
        $emailTemplateRenderer = new TwigRendererAdapter(ROOT . '/Business/Templates/Emails');
        $emailSender = new SendGridAdapter($this->configuration, $this->logger, $emailTemplateRenderer);
        $toasterService = new WebApp\Services\ToasterService\ToasterService();
        $connectedUser = $authenticationGateway->getConnectedUser();

        $request = null;
        $action = null;

        if (isset($_GET['a-request']) && !empty($_GET['a-request'])) {
            $request = $_GET['a-request'];
        }
        if (isset($_POST['a-request']) && !empty($_POST['a-request'])) {
            $request = $_POST['a-request'];
        }
        if (isset($_GET['a-action']) && !empty($_GET['a-action'])) {
            $action = $_GET['a-action'];
        }
        if (isset($_POST['a-action']) && !empty($_POST['a-action'])) {
            $action = $_POST['a-action'];
        }

        $app = AppFactory::create();

        $app->group(
            '/app/ajax/switch.php/api/',
            function (RouteCollectorProxy $group) use (
                $eventService,
                $authenticationGateway,
                $toasterService,
                $emailSender,
                $action
            )
            {
                $group->post(
                    'events/{id:[0-9]*}',
                    function ($request, $response, array $args) use (
                        $eventService,
                        $authenticationGateway,
                        $toasterService,
                        $emailSender,
                        $action
                    )
                    {
                        $eventId = $args['id'] ?? null;
                        $event = new Event((int)$eventId);
                        return (new OneEventController(
                            $this->logger,
                            $eventService,
                            $authenticationGateway,
                            $emailSender,
                            $toasterService,
                            $event
                        ))->computeActionResponseForEvent($action, $eventId);
                    }
                );
            }
        );

        $app->any('/app/ajax/switch.php', fn() => new OkResponse());

        try {
            $app->run();
        } catch (HttpNotFoundException $e) {
            $this->logger->logTrace("not found: {$e->getRequest()->getUri()}");
        }


        switch ($request) {
            case "profile":
                $userId = $_GET['id'] ?? ($_POST['id'] ?? null);
                $user = !is_null($userId) ? User::load($userId) : $connectedUser;
                echo (new ProfileController($this->logger, $authenticationGateway))->getAjax($action, $user);
                break;
            case "notifications":
                echo (new NotificationsCenterController($authenticationGateway))->ajaxSwitch($action);
                break;

            case "search":
                echo (new ResearchController($authenticationGateway, $this->logger))->ajaxRouter($action);
                break;

        }
    }

    /**
     * @throws DatabaseErrorException
     * @throws UserNotExistException
     */
    private function loadUserInAuthenticationGateway(): AuthenticationContext
    {
        $authenticationGateway = new AuthenticationContext();
        if (isset($_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY])) {
            $userId = $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY];
            $connectedUser = User::load($userId);
            $authenticationGateway->setConnectedUser($connectedUser);
        }
        return $authenticationGateway;
    }

    public function configure(ConfigurationInterface $configuration, ContainerBuilderInterface $services): void
    {
        // TODO: Implement configure() method.
    }
}

Host::createDefaultHostBuilder(__DIR__ . '/../../../')
    ->useStartUp(SwitchStartup::class)
    ->configuration(function (ConfigurationBuilderInterface $builder, ConfigurationInterface $configuration) {

        $environment = $configuration['Environment'];
        $version = $configuration['Version'];
        $sentryDsn = $configuration['Sentry:Dsn'];
        if (!is_null($sentryDsn)) {
            init(['dsn' => $sentryDsn, 'environment' => $environment, 'release' => $version]);
        }

        //TIME SYSTEM
        date_default_timezone_set('Europe/Paris');

        // SET PATH
        define('ROOT', substr(__DIR__, 0, strpos(__DIR__, "/WebApp")));
        define('SYS', ROOT . "/system");
        define('APP', ROOT . "/WebApp");
        define('CONF', $configuration->toArray());

        // START SESSION
        session_name("EVENT_PROJECT");
        session_start();

    })
    ->configureLogging(function (LoggingBuilderInterface $builder, ConfigurationInterface $configuration) {
        $sentryLogLevel = $configuration['Sentry:Logging:Level'];
        $builder->addLogger(new SentryLogger($sentryLogLevel ?? SentryLogger::SentryInfo));
        $builder->addLogger(new FileLogger(ROOT . "/../application.log"));
    })
    ->build()
    ->run();




