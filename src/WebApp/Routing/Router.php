<?php


namespace WebApp\Routing
{


    use Adapters\DateTimeProvider\DateTimeProvider;
    use Adapters\Md5PasswordEncoder\Md5PasswordEncoder;
    use Adapters\MySqlDatabase\Repositories\EventRepository;
    use Adapters\MySqlDatabase\Repositories\UserRepository;
    use Adapters\PasswordGenerator\PasswordGenerator;
    use Adapters\SendGrid\SendGridAdapter;
    use Adapters\TokenProvider\TokenProvider;
    use Adapters\TwigRenderer\TwigRendererAdapter;
    use Business\Entities\Event;
    use Business\Ports\DateTimeProviderInterface;
    use Business\Ports\EmailSenderInterface;
    use Business\Ports\PasswordEncoderInterface;
    use Business\Ports\PasswordGeneratorInterface;
    use Business\Ports\TokenProviderInterface;
    use Business\Services\AccountService\AccountService;
    use Business\Services\AccountService\IAccountService;
    use Business\Services\EventService\EventService;
    use Business\Services\EventService\IEventService;
    use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
    use Business\UseCases\SignUp\SignUpUseCase;
    use Business\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
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
    use WebApp\Authentication\AuthenticationContext;
    use WebApp\Authentication\Middlewares\AuthenticationMiddleware;
    use WebApp\Controllers\CreateEventController;
    use WebApp\Controllers\EditEventController;
    use WebApp\Controllers\EventController;
    use WebApp\Controllers\EventExtensions\Extensions\TabAbout;
    use WebApp\Controllers\EventExtensions\Extensions\TabParticipants;
    use WebApp\Controllers\EventExtensions\Extensions\TabPublications;
    use WebApp\Controllers\EventExtensions\Extensions\TabReviews;
    use WebApp\Controllers\EventExtensions\Extensions\TabToDoList;
    use WebApp\Controllers\ForgotPasswordController;
    use WebApp\Controllers\LoginController;
    use WebApp\Controllers\OneEventController;
    use WebApp\Controllers\ProfileController;
    use WebApp\Controllers\SignUpController;
    use WebApp\Controllers\ValidationController;
    use WebApp\Librairies\Emitter;
    use WebApp\Middleware\AccountNotValidatedGuardMiddleware;
    use WebApp\Middleware\AccountValidatedGuardMiddleware;
    use WebApp\Middleware\AlertDisplayMiddleware;
    use WebApp\Middleware\AuthenticatedUserGuardMiddleware;
    use WebApp\Middleware\CreateEventMiddleware;
    use WebApp\Middleware\ErrorManagerMiddleware;
    use WebApp\Middleware\NonAuthenticatedUserGuardMiddleware;
    use WebApp\Middleware\NotificationDisplayMiddleware;
    use WebApp\Middleware\SearchResultsDisplayMiddleware;
    use WebApp\Services\ToasterService\ToasterService;

    class Router
    {
        private ILogger                    $logger;
        private IEventService              $eventService;
        private IAccountService            $accountService;
        private UserRepository             $userRepository;
        private AuthenticationContext      $authenticationGateway;
        private PasswordEncoderInterface   $passwordEncoder;
        private DateTimeProviderInterface  $dateTimeProvider;
        private TokenProviderInterface     $tokenProvider;
        private EmailSenderInterface       $emailSender;
        private PasswordGeneratorInterface $passwordGenerator;
        private ToasterService             $toasterService;

        /**
         * @throws IncorrectConfigurationVariableException
         * @throws ConfigurationVariableNotFoundException
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
            $this->toasterService = new ToasterService();
            $databaseContext = new PDO(
                $configuration->getRequired('Database:ConnectionString'),
                $configuration->getRequired('Database:User'),
                $configuration->getRequired('Database:Password')
            );
            $eventRepository = new EventRepository($databaseContext);
            $this->userRepository = new UserRepository($databaseContext);
            $emailTemplateRenderer = new TwigRendererAdapter(ROOT . '/Business/Templates/Emails');
            $this->emailSender = new SendGridAdapter($configuration, $this->logger, $emailTemplateRenderer);
            $this->passwordEncoder = new Md5PasswordEncoder();
            $this->dateTimeProvider = new DateTimeProvider();
            $this->tokenProvider = new TokenProvider();
            $this->passwordGenerator = new PasswordGenerator();


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
                        $this->logger, $this->userRepository, $this->authenticationGateway, $this->toasterService
                    ))->getView($request);
                });

                $group->post('login', function (Request $request)
                {
                    return (new LoginController(
                        $this->logger, $this->userRepository, $this->authenticationGateway, $this->toasterService
                    ))->login($request);
                });

                $group->get('sign-up', function (Request $request)
                {
                    return (new SignUpController(
                        $this->authenticationGateway, $this->logger, $this->toasterService
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
                    $controller = new SignUpController(
                        $this->authenticationGateway,
                        $this->logger,
                        $this->toasterService
                    );
                    return $controller->signUp($request, $useCase);
                });

                $group->get('reset-password', function ()
                {
                    return (new ForgotPasswordController(
                        $this->authenticationGateway,
                        $this->logger,
                        $this->toasterService
                    ))->getView();
                });

                $group->post('reset-password', function (Request $request)
                {
                    $useCase = new AskNewPasswordUseCase(
                        $this->userRepository,
                        $this->passwordGenerator,
                        $this->passwordEncoder,
                        $this->emailSender
                    );
                    return (new ForgotPasswordController(
                        $this->authenticationGateway,
                        $this->logger,
                        $this->toasterService
                    ))->resetPassword($request, $useCase);
                });
            })->addMiddleware(new NonAuthenticatedUserGuardMiddleware($this->authenticationGateway))->addMiddleware(
                new AlertDisplayMiddleware($this->toasterService)
            )->addMiddleware(new AuthenticationMiddleware($this->authenticationGateway))->addMiddleware(
                new ErrorManagerMiddleware($this->logger)
            );

            $routeCollectorProxy->group('/', function (RouteCollectorProxy $group) use ($configuration)
            {
                $group->get('', fn() => RedirectedResponse::to('/events'));

                $group->post('events', function (Request $request)
                {
                    return (new CreateEventController(
                        $this->logger, $this->eventService, $this->authenticationGateway, $this->toasterService
                    ))->createEvent($request);
                })->add(new AccountValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('events[/{id:[0-9]*}]', function ($request, $response, array $args) use ($configuration)
                {
                    $eventId = $args['id'] ?? null;

                    if (!is_null($eventId)) {
                        $event = new Event($eventId);
                        $eventExtensions = [
                            new TabParticipants(
                                $this->emailSender,
                                $this->authenticationGateway,
                                $event,
                                $this->toasterService
                            ),
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
                            new TabParticipants(
                                $this->emailSender,
                                $this->authenticationGateway,
                                $event,
                                $this->toasterService
                            ),
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
                        $this->authenticationGateway,
                        $this->toasterService
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
                        $this->logger, $this->accountService, $this->authenticationGateway, $this->toasterService
                    );
                    return $controller->index($request, $useCase);
                })->add(new AccountNotValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('validation/new-token', function ()
                {
                    return (new ValidationController(
                        $this->logger, $this->accountService, $this->authenticationGateway, $this->toasterService
                    ))->askNewValidationToken();
                })->add(new AccountNotValidatedGuardMiddleware($this->authenticationGateway));

                $group->get('disconnect', function (Request $request, Response $response)
                {
                    session_unset();
                    return RedirectedResponse::to('/');
                });
            })
                                ->addMiddleware(new AlertDisplayMiddleware($this->toasterService))
                                ->addMiddleware(new NotificationDisplayMiddleware($this->authenticationGateway))
                                ->addMiddleware(new SearchResultsDisplayMiddleware($this->authenticationGateway))
                                ->addMiddleware(
                                    new CreateEventMiddleware(
                                        $this->logger,
                                        $this->eventService,
                                        $this->authenticationGateway,
                                        $this->toasterService
                                    )
                                )
                                ->addMiddleware(new AuthenticatedUserGuardMiddleware($this->authenticationGateway))
                                ->addMiddleware(new AuthenticationMiddleware($this->authenticationGateway))
                                ->addMiddleware(new ErrorManagerMiddleware($this->logger));
        }
    }
}