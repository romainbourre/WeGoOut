<?php

namespace WebApp\Controllers
{


    use Business\Ports\EventCategoryRepositoryInterface;
    use Business\Services\EventService\IEventService;
    use Exception;
    use Slim\Psr7\Response;
    use System\Logging\LoggerInterface;
    use WebApp\Attributes\Page;
    use WebApp\Authentication\AuthenticationContext;

    class EditEventController extends AppController
    {

        private LoggerInterface $logger;
        private IEventService $eventService;

        public function __construct(
            LoggerInterface                                   $logger,
            IEventService                                     $eventService,
            private readonly AuthenticationContext            $authenticationGateway,
            private readonly EventCategoryRepositoryInterface $categoryRepository
        ) {
            parent::__construct();
            $this->logger = $logger;
            $this->eventService = $eventService;
        }

        #[Page('edit-event.css', 'edit-event.js')]
        public function getView(string $eventId): Response
        {
            try
            {
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
                $event = $this->eventService->getEvent($eventId);

                // WEB PAGE NAME
                $titleWebPage = "Édition - " . $event->getTitle();

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));
                $categories = $this->categoryRepository->all();
                $content = $this->render('listevent.edit-event.view-edit-event', compact('event', 'categories'));

                $view = $this->render('templates.template', compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content', 'connectedUser'));

                return $this->ok($view);

            }
            catch (Exception $e)
            {
                $this->logger->logCritical($e->getMessage());
                return $this->internalServerError()->withRedirectTo("/events/$eventId");
            }
        }
    }
}
