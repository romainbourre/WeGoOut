<?php


namespace App\Routing
{


    use App\Controllers\CreateEventController;
    use App\Controllers\EditEventController;
    use App\Controllers\EventController;
    use App\Controllers\ForgotPasswordController;
    use App\Controllers\LoginController;
    use App\Controllers\OneEventController;
    use App\Controllers\ProfileController;
    use App\Controllers\SignUpController;
    use App\Controllers\ValidationController;
    use App\Librairies\Emitter;
    use App\Middleware\AccountNotValidatedGuardMiddleware;
    use App\Middleware\AccountValidatedGuardMiddleware;
    use App\Middleware\AlertDisplayMiddleware;
    use App\Middleware\AuthenticatedUserGuardMiddleware;
    use App\Middleware\CreateEventMiddleware;
    use App\Middleware\ErrorManagerMiddleware;
    use App\Middleware\NonAuthenticatedUserGuardMiddleware;
    use App\Middleware\NotificationDisplayMiddleware;
    use App\Middleware\SearchResultsDisplayMiddleware;
    use Domain\Services\AccountService\AccountService;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\EventService\EventService;
    use Domain\Services\EventService\IEventService;
    use Infrastructure\MySqlDatabase\Repositories\EventRepository;
    use Infrastructure\MySqlDatabase\Repositories\UserRepository;
    use Infrastructure\SendGrid\SendGridAdapter;
    use Infrastructure\TwigRenderer\TwigRendererAdapter;
    use PDO;
    use System\Configuration\IConfiguration;
    use Slim\Interfaces\RouteCollectorProxyInterface;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use Slim\Routing\RouteCollectorProxy;
    use System\Exceptions\ConfigurationVariableNotFoundException;
    use System\Exceptions\IncorrectConfigurationVariableException;
    use System\Logging\ILogger;
    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\OkResponse;

    class Router
    {
        private ILogger         $logger;
        private IEventService   $eventService;
        private IAccountService $accountService;

        /**
         * Router constructor.
         */
        public function __construct(ILogger $logger, RouteCollectorProxyInterface $routeCollectorProxy, IConfiguration $configuration)
        {
            $this->logger = $logger;
            $this->configureService($configuration);
            $this->configure($configuration, $routeCollectorProxy);
        }

        /**
         * @throws IncorrectConfigurationVariableException
         * @throws ConfigurationVariableNotFoundException
         */
        private function configureService(IConfiguration $configuration): void
        {
            $connectionString = $configuration->getRequired('Database:ConnectionString');
            $databaseContext = new PDO($connectionString, $configuration->getRequired('Database:User'), $configuration->getRequired('Database:Password'));
            $eventRepository = new EventRepository($databaseContext);
            $userRepository = new UserRepository($databaseContext);
            $emailSender = new SendGridAdapter($configuration['SendGrid:ApiKey'], $this->logger);
            $emailTemplateRenderer = new TwigRendererAdapter(ROOT . '/domain/Templates/Emails');

            $this->eventService = new EventService($eventRepository, Emitter::getInstance());
            $this->accountService = new AccountService($userRepository, $emailSender, $emailTemplateRenderer);
        }

        private function configure(IConfiguration $configuration, RouteCollectorProxyInterface $routeCollectorProxy): void
        {
            $routeCollectorProxy->any('/app/ajax/switch.php', fn() => new OkResponse());

            $routeCollectorProxy->group('/', function (RouteCollectorProxy $group) use ($configuration)
            {
                $group->get('login', function (Request $request)
                {
                    return (new LoginController($this->logger, $this->accountService))->getView($request);
                });

                $group->post('login', function (Request $request)
                {
                    return (new LoginController($this->logger, $this->accountService))->login($request);
                });

                $group->get('sign-up', function (Request $request)
                {
                    return (new SignUpController($this->logger, $this->accountService))->getView($request);
                });

                $group->post('sign-up', function (Request $request)
                {
                    return (new SignUpController($this->logger, $this->accountService))->signUp($request);
                });

                $group->get('reset-password', function ()
                {
                    return (new ForgotPasswordController($this->logger, $this->accountService))->getView();
                });

                $group->post('reset-password', function (Request $request)
                {
                    return (new ForgotPasswordController($this->logger, $this->accountService))->resetPassword($request);
                });
            })->addMiddleware(new NonAuthenticatedUserGuardMiddleware())->addMiddleware(new AlertDisplayMiddleware())->addMiddleware(new ErrorManagerMiddleware($this->logger));

            $routeCollectorProxy->group('/', function (RouteCollectorProxy $group) use ($configuration)
            {

                $group->get('', fn() => (new OkResponse())->withRedirectTo('/events'));

                $group->post('events', function (Request $request)
                {
                    return (new CreateEventController($this->logger, $this->eventService))->createEvent($request);

                })->add(new AccountValidatedGuardMiddleware());

                $group->get('events[/{id:[0-9]*}]', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'] ?? null;

                    if (!is_null($eventId))
                    {
                        return (new OneEventController($this->logger, $this->eventService))->getView($eventId);
                    }

                    return (new EventController($configuration, $this->logger, $this->eventService))->getView();

                })->add(new AccountValidatedGuardMiddleware());

                $group->put('events/{id:[0-9]*}/register', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'];

                    if (is_null($eventId))
                    {
                        return new BadRequestResponse("uri argument id not found.");
                    }

                    return (new OneEventController($this->logger, $this->eventService))->subscribeToEvent($eventId);
                })->add(new AccountValidatedGuardMiddleware());

                $group->get('events/{id:[0-9]*}/edit', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'] ?? null;

                    if (!is_null($eventId))
                    {
                        return (new EditEventController($this->logger, $this->eventService))->getView($eventId);
                    }

                    return (new EventController($configuration, $this->logger, $this->eventService))->getView();

                })->add(new AccountValidatedGuardMiddleware());

                $group->get('events/create-form', function () {
                    $controller = new CreateEventController($this->logger, $this->eventService);
                    return $controller->getCreateEventForm();
                })->add(new AccountValidatedGuardMiddleware());

                $group->post('events/view', function (Request $request) use ($configuration)
                {
                    $controller = new EventController($configuration, $this->logger, $this->eventService);
                    return $controller->searchEvents($request);
                })->add(new AccountValidatedGuardMiddleware());

                $group->any('profile[/{id}]', function (Request $request, Response $response, array $args)
                {
                    $id = $args['id'] ?? null;
                    return (new ProfileController($this->logger))->getView($id);
                })->add(new AccountValidatedGuardMiddleware());

                $group->get('validation', function (Request $request)
                {
                    return (new ValidationController($this->logger, $this->accountService))->getView($request);
                })->add(new AccountNotValidatedGuardMiddleware());

                $group->get('validation/new-token', function ()
                {
                    return (new ValidationController($this->logger, $this->accountService))->askNewValidationToken();
                })->add(new AccountNotValidatedGuardMiddleware());

                $group->get('disconnect', function (Request $request, Response $response)
                {
                    session_unset();
                    return $response->withAddedHeader('Location', '/');
                });

            })
                                ->addMiddleware(new AlertDisplayMiddleware())
                                ->addMiddleware(new NotificationDisplayMiddleware())
                                ->addMiddleware(new SearchResultsDisplayMiddleware())
                                ->addMiddleware(new CreateEventMiddleware($this->logger, $this->eventService))
                                ->addMiddleware(new AuthenticatedUserGuardMiddleware())
                                ->addMiddleware(new ErrorManagerMiddleware($this->logger));
        }
    }
}