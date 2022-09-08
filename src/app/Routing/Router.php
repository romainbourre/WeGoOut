<?php


namespace App\Routing
{


    use App\Authentication\AuthenticationContext;
    use App\Authentication\Middlewares\AuthenticationMiddleware;
    use App\Controllers\CreateEventController;
    use App\Controllers\EditEventController;
    use App\Controllers\EventController;
    use App\Controllers\EventExtensions\Extensions\TabAbout;
    use App\Controllers\EventExtensions\Extensions\TabParticipants;
    use App\Controllers\EventExtensions\Extensions\TabPublications;
    use App\Controllers\EventExtensions\Extensions\TabReviews;
    use App\Controllers\EventExtensions\Extensions\TabToDoList;
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
    use Domain\Entities\Event;
    use Domain\Interfaces\DateTimeProviderInterface;
    use Domain\Interfaces\IEmailSender;
    use Domain\Interfaces\IUserRepository;
    use Domain\Interfaces\PasswordEncoderInterface;
    use Domain\Interfaces\TokenProviderInterface;
    use Domain\Services\AccountService\AccountService;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\EventService\EventService;
    use Domain\Services\EventService\IEventService;
    use Domain\UseCases\SignUp\SignUpUseCase;
    use Domain\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
    use Infrastructure\DateTimeProvider\DateTimeProvider;
    use Infrastructure\Md5PasswordEncoder\Md5PasswordEncoder;
    use Infrastructure\MySqlDatabase\Repositories\EventRepository;
    use Infrastructure\MySqlDatabase\Repositories\UserRepository;
    use Infrastructure\SendGrid\SendGridAdapter;
    use Infrastructure\TokenProvider\TokenProvider;
    use Infrastructure\TwigRenderer\TwigRendererAdapter;
    use PDO;
    use PhpLinq\PhpLinq;
    use Slim\Interfaces\RouteCollectorProxyInterface;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use Slim\Routing\RouteCollectorProxy;
    use System\Configuration\IConfiguration;
    use System\Exceptions\ConfigurationVariableNotFoundException;
    use System\Exceptions\IncorrectConfigurationVariableException;
    use System\Logging\ILogger;
    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\OkResponse;
    use System\Routing\Responses\RedirectedResponse;

    class Router
    {
        private ILogger                   $logger;
        private IEventService             $eventService;
        private IAccountService           $accountService;
        private IUserRepository           $userRepository;
        private AuthenticationContext     $authenticationGateway;
        private PasswordEncoderInterface  $passwordEncoder;
        private DateTimeProviderInterface $dateTimeProvider;
        private TokenProviderInterface    $tokenProvider;
        private IEmailSender              $emailSender;


        /**
         * Router constructor.
         */
        public function __construct(
            ILogger $logger,
            RouteCollectorProxyInterface $routeCollectorProxy,
            IConfiguration $configuration
        ) {
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
            $databaseContext = new PDO(
                $configuration->getRequired('Database:ConnectionString'),
                $configuration->getRequired('Database:User'),
                $configuration->getRequired('Database:Password')
            );
            $eventRepository = new EventRepository($databaseContext);
            $this->userRepository = new UserRepository($databaseContext);
            $emailTemplateRenderer = new TwigRendererAdapter(ROOT . '/domain/Templates/Emails');
            $this->emailSender = new SendGridAdapter($configuration, $this->logger, $emailTemplateRenderer);
            $this->passwordEncoder = new Md5PasswordEncoder();
            $this->dateTimeProvider = new DateTimeProvider();
            $this->tokenProvider = new TokenProvider();


            $this->authenticationGateway = new AuthenticationContext();
            $this->eventService = new EventService(
                $this->authenticationGateway,
                $eventRepository,
                Emitter::getInstance()
            );
            $this->accountService = new AccountService(
                $this->userRepository, $this->emailSender, $emailTemplateRenderer
            );
        }

        private function configure(
            IConfiguration $configuration,
            RouteCollectorProxyInterface $routeCollectorProxy
        ): void {
            $routeCollectorProxy->any('/app/ajax/switch.php', fn() => new OkResponse());

            $routeCollectorProxy->group('/', function (RouteCollectorProxy $group) use ($configuration)
            {
                $group->get('login', function (Request $request)
                {
                    return (new LoginController(
                        $this->logger, $this->userRepository, $this->authenticationGateway
                    ))->getView($request);
                });

                $group->post('login', function (Request $request)
                {
                    return (new LoginController(
                        $this->logger, $this->userRepository, $this->authenticationGateway
                    ))->login($request);
                });

                $group->get('sign-up', function (Request $request)
                {
                    return (new SignUpController(
                        $this->authenticationGateway, $this->logger, $this->accountService
                    ))->getView($request);
                });

                $group->post('sign-up', function (Request $request)
                {
                    $useCase = new SignUpUseCase(
                        $this->passwordEncoder,
                        $this->dateTimeProvider,
                        $this->tokenProvider,
                        $this->emailSender,
                        $this->userRepository
                    );
                    $controller = new SignUpController($this->authenticationGateway, $this->logger);
                    return $controller->signUp($request, $useCase);
                });

                $group->get('reset-password', function ()
                {
                    return (new ForgotPasswordController(
                        $this->authenticationGateway,
                        $this->logger,
                        $this->accountService
                    ))->getView();
                });

                $group->post('reset-password', function (Request $request)
                {
                    return (new ForgotPasswordController(
                        $this->authenticationGateway,
                        $this->logger,
                        $this->accountService
                    ))->resetPassword($request);
                });
            })->addMiddleware(new NonAuthenticatedUserGuardMiddleware($this->authenticationGateway))->addMiddleware(
                new AlertDisplayMiddleware()
            )->addMiddleware(new AuthenticationMiddleware($this->authenticationGateway))->addMiddleware(
                new ErrorManagerMiddleware($this->logger)
            );

            $routeCollectorProxy->group('/', function (RouteCollectorProxy $group) use ($configuration)
            {
                $group->get('', fn() => RedirectedResponse::to('/events'));

                $group->post('events', function (Request $request)
                {
                    return (new CreateEventController(
                        $this->logger, $this->eventService, $this->authenticationGateway
                    ))->createEvent($request);
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('events[/{id:[0-9]*}]', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'] ?? null;

                    if (!is_null($eventId)) {
                        $event = new Event($eventId);
                        $eventExtensions = [
                            new TabParticipants($this->emailSender, $this->authenticationGateway, $event),
                            new TabPublications($this->authenticationGateway, $event),
                            new TabToDoList($this->authenticationGateway, $event),
                            new TabReviews($this->authenticationGateway, $event),
                            new TabAbout($this->authenticationGateway, $event)
                        ];
                        return (new OneEventController(
                            $this->logger,
                            $this->eventService,
                            $this->authenticationGateway,
                            PhpLinq::fromArray($eventExtensions)
                        ))->getView($eventId);
                    }

                    return (new EventController(
                        $configuration,
                        $this->logger,
                        $this->eventService,
                        $this->authenticationGateway
                    ))->getView();
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->put(
                    'events/{id:[0-9]*}/register',
                    function ($request, $response, array $args) use ($configuration)
                    {
                        $eventId = $args['id'];

                        if (is_null($eventId)) {
                            return new BadRequestResponse("uri argument id not found.");
                        }

                        $event = new Event($eventId);
                        $eventExtensions = [
                            new TabParticipants($this->emailSender, $this->authenticationGateway, $event),
                            new TabPublications($this->authenticationGateway, $event),
                            new TabToDoList($this->authenticationGateway, $event),
                            new TabReviews($this->authenticationGateway, $event),
                            new TabAbout($this->authenticationGateway, $event)
                        ];
                        return (new OneEventController(
                            $this->logger,
                            $this->eventService,
                            $this->authenticationGateway,
                            PhpLinq::fromArray($eventExtensions)
                        ))->subscribeToEvent($eventId);
                    }
                )->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('events/{id:[0-9]*}/edit', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'] ?? null;

                    if (!is_null($eventId)) {
                        return (new EditEventController(
                            $this->logger, $this->eventService, $this->authenticationGateway
                        ))->getView($eventId);
                    }

                    return (new EventController(
                        $configuration,
                        $this->logger,
                        $this->eventService,
                        $this->authenticationGateway
                    ))->getView();
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('events/create-form', function ()
                {
                    $controller = new CreateEventController(
                        $this->logger,
                        $this->eventService,
                        $this->authenticationGateway
                    );
                    return $controller->getCreateEventForm();
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->post('events/view', function (Request $request) use ($configuration)
                {
                    $controller = new EventController(
                        $configuration,
                        $this->logger,
                        $this->eventService,
                        $this->authenticationGateway
                    );
                    return $controller->searchEvents($request);
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->any('profile[/{id}]', function (Request $request, Response $response, array $args)
                {
                    $id = $args['id'] ?? null;
                    return (new ProfileController($this->logger, $this->authenticationGateway))->getView($id);
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('validation', function (Request $request)
                {
                    $useCase = new ValidateUserAccountUseCase($this->authenticationGateway, $this->userRepository);
                    $controller = new ValidationController(
                        $this->logger, $this->accountService, $this->authenticationGateway
                    );
                    return $controller->index($request, $useCase);
                })->add(new AccountNotValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('validation/new-token', function ()
                {
                    return (new ValidationController(
                        $this->logger, $this->accountService, $this->authenticationGateway
                    ))->askNewValidationToken();
                })->add(new AccountNotValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('disconnect', function (Request $request, Response $response)
                {
                    session_unset();
                    return RedirectedResponse::to('/');
                });
            })
                                ->addMiddleware(new AlertDisplayMiddleware())
                                ->addMiddleware(new NotificationDisplayMiddleware($this->authenticationGateway))
                                ->addMiddleware(new SearchResultsDisplayMiddleware($this->authenticationGateway))
                                ->addMiddleware(
                                    new CreateEventMiddleware(
                                        $this->logger,
                                        $this->eventService,
                                        $this->authenticationGateway
                                    )
                                )
                                ->addMiddleware(new AuthenticatedUserGuardMiddleware($this->authenticationGateway))
                                ->addMiddleware(new AuthenticationMiddleware($this->authenticationGateway))
                                ->addMiddleware(new ErrorManagerMiddleware($this->logger));
        }
    }
}