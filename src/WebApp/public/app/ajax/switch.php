<?php

require_once '../../../../../vendor/autoload.php';

use Adapters\MySqlDatabase\Repositories\EventRepository;
use Adapters\SendGrid\SendGridAdapter;
use Adapters\TwigRenderer\TwigRendererAdapter;
use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\UserNotExistException;
use Business\Ports\AuthenticationContextInterface;
use Business\Services\EventService\EventService;
use System\Configuration\IConfiguration;
use System\Configuration\IConfigurationBuilder;
use System\Host\Host;
use System\Host\IStartUp;
use System\Logging\ILogger;
use System\Logging\ILoggingBuilder;
use WebApp\Authentication\AuthenticationConstants;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Controllers\EventExtensions\Extensions\TabAbout;
use WebApp\Controllers\EventExtensions\Extensions\TabParticipants;
use WebApp\Controllers\EventExtensions\Extensions\TabPublications;
use WebApp\Controllers\EventExtensions\Extensions\TabReviews;
use WebApp\Controllers\EventExtensions\Extensions\TabToDoList;
use WebApp\Controllers\NotificationsCenterController;
use WebApp\Controllers\OneEventController;
use WebApp\Controllers\ProfileController;
use WebApp\Controllers\ResearchController;
use WebApp\Librairies\Emitter;
use WebApp\Logging\Logger\SentryLogger;

use function Sentry\init;

error_reporting(E_ALL ^ E_DEPRECATED);

class SwitchStartup implements IStartUp
{
    private IConfiguration $configuration;
    private ILogger        $logger;

    public function __construct(IConfiguration $configuration, ILogger $logger)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function run(): void
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

        $this->logger->logInfo(implode($_POST));
        switch($request) {
            case "event":
                $eventId = $_GET['id'] ?? ($_POST['id'] ?? null);
                if(!is_null($eventId)) {
                    $event = new Event((int)$eventId);
                    $eventExtensions = [
                        new TabParticipants($emailSender, $authenticationGateway, $event, $toasterService),
                        new TabPublications($authenticationGateway, $event),
                        new TabToDoList($authenticationGateway, $event),
                        new TabReviews($authenticationGateway, $event),
                        new TabAbout($authenticationGateway, $event)
                    ];
                    echo (new OneEventController(
                        $this->logger,
                        $eventService,
                        $authenticationGateway,
                        PhpLinq\PhpLinq::fromArray($eventExtensions)
                    ))->getAjaxEventView($action, $eventId);
                }
                break;
            case "profile":
                $userId = $_GET['id'] ?? ($_POST['id'] ?? null);
                $user = !is_null($userId) ? User::load($userId) : $connectedUser;
                echo (new ProfileController($this->logger, $authenticationGateway))->getAjax($action, $user);
                break;
            case "notifications":
                echo (new NotificationsCenterController($authenticationGateway))->ajaxSwitch($action);
                break;

            case "search":
                echo (new ResearchController($authenticationGateway))->ajaxRouter($action);
                break;

        }
    }

    /**
     * @throws DatabaseErrorException
     * @throws UserNotExistException
     */
    private function loadUserInAuthenticationGateway(): AuthenticationContextInterface
    {
        $authenticationGateway = new AuthenticationContext();
        if (isset($_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY])) {
            $userId = $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY];
            $connectedUser = User::load($userId);
            $authenticationGateway->setConnectedUser($connectedUser);
        }
        return $authenticationGateway;
    }
}

Host::createDefaultHostBuilder(__DIR__ . '/../../../')
    ->useStartUp(SwitchStartup::class)
    ->configuration(function (IConfigurationBuilder $builder, IConfiguration $configuration) {

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
    ->configureLogging(function(ILoggingBuilder $builder, IConfiguration $configuration)
    {
        $sentryLogLevel = $configuration['Sentry:Logging:Level'];
        $builder->addLogger(new SentryLogger($sentryLogLevel ?? SentryLogger::SentryInfo));
    })
    ->build()
    ->run();




