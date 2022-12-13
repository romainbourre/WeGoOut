<?php


namespace WebApp\Routing {


    use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
    use Business\UseCases\SignUp\SignUpUseCase;
    use Business\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
    use Psr\Container\ContainerExceptionInterface;
    use Psr\Container\NotFoundExceptionInterface;
    use Slim\Factory\AppFactory;
    use Slim\Interfaces\RouteCollectorProxyInterface;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use Slim\Routing\RouteCollectorProxy;
    use System\Configuration\ConfigurationInterface;
    use System\DependencyInjection\ContainerInterface;
    use System\Routing\Responses\BadRequestResponse;
    use System\Routing\Responses\OkResponse;
    use System\Routing\Responses\RedirectedResponse;
    use WebApp\Authentication\Middlewares\AuthenticationMiddleware;
    use WebApp\Controllers\CreateEventController;
    use WebApp\Controllers\EditEventController;
    use WebApp\Controllers\EventController;
    use WebApp\Controllers\ForgotPasswordController;
    use WebApp\Controllers\LoginController;
    use WebApp\Controllers\ProfileController;
    use WebApp\Controllers\SignUpController;
    use WebApp\Controllers\ValidationController;
    use WebApp\Middleware\AccountNotValidatedGuardMiddleware;
    use WebApp\Middleware\AccountValidatedGuardMiddleware;
    use WebApp\Middleware\AccountValidatedGuardMiddlewareAsUnauthorized;
    use WebApp\Middleware\AlertDisplayMiddleware;
    use WebApp\Middleware\AuthenticatedUserGuardMiddleware;
    use WebApp\Middleware\CreateEventMiddleware;
    use WebApp\Middleware\ErrorManagerMiddleware;
    use WebApp\Middleware\NonAuthenticatedUserGuardMiddleware;
    use WebApp\Middleware\NotificationDisplayMiddleware;
    use WebApp\Middleware\SearchResultsDisplayMiddleware;

    readonly class SlimFrameworkRouter
    {

        private RouteCollectorProxyInterface $routeCollectorProxy;

        public function __construct(private ContainerInterface $container, private ConfigurationInterface $configuration)
        {
            $this->routeCollectorProxy = AppFactory::create();
            $this->routeCollectorProxy->addBodyParsingMiddleware();

        }

        /**
         * @throws ContainerExceptionInterface
         * @throws NotFoundExceptionInterface
         */
        public function configure(): RouteCollectorProxyInterface|\Slim\App
        {
            $this->routeCollectorProxy->any('/app/ajax/switch.php', fn() => new OkResponse());

            $this->routeCollectorProxy->group('/', function (RouteCollectorProxy $group) {
                $group->get('login', function (Request $request) {
                    return $this->container->get(LoginController::class)->getView($request);
                });

                $group->post('login', function (Request $request) {
                    return $this->container->get(LoginController::class)->login($request);
                });

                $group->get('sign-up', function (Request $request) {
                    return $this->container->get(SignUpController::class)->getView($request);
                });

                $group->post('sign-up', function (Request $request) {
                    return $this->container->get(SignUpController::class)->signUp($request, $this->container->get(SignUpUseCase::class));
                });

                $group->get('reset-password', function () {
                    return $this->container->get(ForgotPasswordController::class)->getView();
                });

                $group->post('reset-password', function (Request $request) {
                    return $this->container->get(ForgotPasswordController::class)->resetPassword($request, $this->container->get(AskNewPasswordUseCase::class));
                });
            })
                ->addMiddleware($this->container->get(NonAuthenticatedUserGuardMiddleware::class))
                ->addMiddleware($this->container->get(AlertDisplayMiddleware::class))
                ->addMiddleware($this->container->get(AuthenticationMiddleware::class))
                ->addMiddleware($this->container->get(ErrorManagerMiddleware::class));

            $this->routeCollectorProxy->group('/', function (RouteCollectorProxy $group) {
                $group->get('', fn() => RedirectedResponse::to('/events'));

                $group->post('events', function (Request $request) {
                    return $this->container->get(CreateEventController::class)->createEvent($request);
                })->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->get('events[/{id:[0-9]*}]', function ($request, $response, array $args) {
                    $eventId = $args['id'] ?? null;
                    /**
                     * @var EventController $eventController
                     */
                    $eventController = $this->container->get(EventController::class);
                    if (!is_null($eventId)) {
                        return $eventController->forEvent($eventId)->getView($eventId);
                    }
                    return $eventController->getView();
                })->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->put(
                    'events/{id:[0-9]*}/register',
                    function ($request, $response, array $args) {
                        $eventId = $args['id'];
                        if (is_null($eventId)) {
                            return new BadRequestResponse("uri argument id not found.");
                        }
                        return $this->container->get(EventController::class)->forEvent($eventId)->subscribeToEvent($eventId);
                    }
                )->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->get('events/{id:[0-9]*}/edit', function ($request, $response, array $args) {
                    $eventId = $args['id'] ?? null;
                    if (!is_null($eventId)) {
                        return $this->container->get(EditEventController::class)->getView($eventId);
                    }
                    return $this->container->get(EventController::class)->getView();
                })->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->get('events/create-form', function () {
                    return $this->container->get(CreateEventController::class)->getCreateEventForm();
                })->add($this->container->get(AccountValidatedGuardMiddlewareAsUnauthorized::class));

                $group->post('events/view', function (Request $request) {
                    return $this->container->get(EventController::class)->searchEvents($request);
                })->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->any('profile[/{id}]', function (Request $request, Response $response, array $args) {
                    $id = $args['id'] ?? null;
                    return $this->container->get(ProfileController::class)->getView($id);
                })->add($this->container->get(AccountValidatedGuardMiddleware::class));

                $group->get('validation', function (Request $request) {
                    return $this->container->get(ValidationController::class)->index($request, $this->container->get(ValidateUserAccountUseCase::class));
                })->add($this->container->get(AccountNotValidatedGuardMiddleware::class));

                $group->get('validation/new-token', function () {
                    return $this->container->get(ValidationController::class)->askNewValidationToken();
                })->add($this->container->get(AccountNotValidatedGuardMiddleware::class));

                $group->get('disconnect', function (Request $request, Response $response) {
                    session_unset();
                    return RedirectedResponse::to('/');
                });
            })
                ->addMiddleware($this->container->get(AlertDisplayMiddleware::class))
                ->addMiddleware($this->container->get(NotificationDisplayMiddleware::class))
                ->addMiddleware($this->container->get(SearchResultsDisplayMiddleware::class))
                ->addMiddleware($this->container->get(CreateEventMiddleware::class)
                )
                ->addMiddleware($this->container->get(AuthenticatedUserGuardMiddleware::class))
                ->addMiddleware($this->container->get(AuthenticationMiddleware::class))
                ->addMiddleware($this->container->get(ErrorManagerMiddleware::class));

            return $this->routeCollectorProxy;
        }
    }
}
