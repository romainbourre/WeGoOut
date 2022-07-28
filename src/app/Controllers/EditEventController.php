<?php

namespace App\Controllers
{


    use Domain\Services\EventService\IEventService;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Response;

    class EditEventController extends AppController
    {

        /**
         * @var ILogger
         */
        private ILogger       $logger;
        private IEventService $eventService;

        /**
         * EditEvent constructor.
         * @param ILogger $logger
         * @param IEventService $eventService
         */
        public function __construct(ILogger $logger, IEventService $eventService)
        {
            parent::__construct();
            $this->logger = $logger;
            $this->eventService = $eventService;
        }

        /**
         * Compose edition web page of one event
         * @param string $eventId
         * @return Response
         */
        public function getView(string $eventId): Response
        {
            try
            {

                $event = $this->eventService->getEvent($eventId);

                // WEB PAGE NAME
                $titleWebPage = "Édition - " . $event->getTitle();

                // LOAD CSS AND JS FILE
                $this->addCssStyle('css-editevent.css');
                $this->addJsScript('js-editevent.js');

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));

                $content = $this->render('listevent.edit-event.view-edit-event', compact('event'));

                $view = $this->render('templates.template', compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content'));

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