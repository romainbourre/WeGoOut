<?php


namespace WebApp\Controllers
{


    use Business\Entities\Event;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\EventCanceledException;
    use Business\Exceptions\EventDeletedException;
    use Business\Exceptions\EventNotExistException;
    use Business\Exceptions\EventSignaledException;
    use Business\Exceptions\ResourceNotFound;
    use Business\Ports\AuthenticationContextInterface;
    use Business\Services\EventService\IEventService;
    use Exception;
    use PhpLinq\Interfaces\ILinq;
    use System\Logging\ILogger;
    use System\Routing\Responses\NotFoundResponse;
    use System\Routing\Responses\OkResponse;
    use System\Routing\Responses\Response;
    use WebApp\Controllers\EventExtensions\EventExtension;
    use WebApp\Controllers\EventExtensions\IEventExtension;

    /**
     * Class OneEvent
     * Represent page of an event
     * @package App\Controllers
     * @author Bourré Romain
     */
    class OneEventController extends AppController
    {

        public function __construct(
            private readonly ILogger $logger,
            private readonly IEventService $eventService,
            private readonly AuthenticationContextInterface $authenticationGateway,
            private readonly ILinq $extensions
        ) {
            parent::__construct();
        }

        /**
         * Generate global view for one event
         * @param string $eventId
         * @return Response
         */
        public function getView(string $eventId): Response
        {
            try {
                $event = new Event($eventId);
                $connectedUser = $this->authenticationGateway->getConnectedUser();

                $titleWebPage = CONF['Application']['Name'] . " - " . $event->getTitle();

                // LOAD CSS AND JS FILE
                $this->addCssStyle('css-listevent.css');
                $this->addCssStyle('css-oneevent.css');
                $this->addJsScript('js-listevent.js');
                $this->addJsScript('one-event.js');

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));

                if ($this->globalConfidentiality($event)) {
                    // GET USER LOCATION
                    $userLocation = $connectedUser->getLocation();

                    // GET NUMBER OF PARTICIPANT FOR PANEL
                    $numbPartItem = $this->getViewEventNumbPart($event);

                    // GET COMMAND FOR REGISTRATION AND EDIT
                    $registrationCmd = $this->getViewRegistrationCmd($event);

                    // GET WINDOW OF EVENT
                    $contentWindow = $this->getViewEventWindow($event);

                    $content = $this->render(
                        'listevent.view-one-event',
                        compact(
                            'event',
                            'userLocation',
                            'registrationCmd',
                            'numbPartItem',
                            'contentWindow',
                            'connectedUser'
                        )
                    );
                } else {
                    $content = $this->render('listevent.one-event.event-not-auth');
                }

                $view = $this->render(
                    'templates.template',
                    compact(
                        'titleWebPage',
                        'userMenu',
                        'navUserDropDown',
                        'navAddEvent',
                        'navItems',
                        'content',
                        'connectedUser'
                    )
                );

                return $this->ok($view);
            } catch (EventCanceledException|EventSignaledException|EventNotExistException|EventDeletedException $exception) {
                $this->logger->logError($exception->getMessage(), $exception);
                return $this->internalServerError();
            } catch (Exception $exception) {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }

        /**
         * Check confidentiality for global view
         * @param Event $event
         * @return bool
         * @throws DatabaseErrorException
         */
        private function globalConfidentiality(Event $event): bool
        {
            $currentUser = $this->authenticationGateway->getConnectedUser();
            return (
                $event->isCreator($currentUser) ||
                $event->isPublic() ||
                ($event->isPrivate() && (
                        $event->isInvited($currentUser) ||
                        ($event->getUser()->isFriend($currentUser) &&
                            !$event->isGuestOnly()
                        )
                    )
                )
            );
        }

        /**
         * @throws Exception
         */
        private function getViewRegistrationCmd(Event $event): string
        {
            $currentUser = $this->authenticationGateway->getConnectedUser();
            $view = '';
            if (!$event->isStarted() && !$event->isOver()) {
                if ($event->isCreator($currentUser) || $event->isOrganizer($currentUser)) {
                    $view = $this->render('listevent.one-event.cmd-edit', compact('event'));
                } elseif ($event->isParticipantWait($currentUser)) {
                    $view = $this->render('listevent.one-event.cmd-wait-enrolled');
                } elseif (!$event->isParticipant($currentUser)) {
                    $view = $this->render("listevent.one-event.cmd-not-enrolled");
                } elseif ($event->isParticipantValid($currentUser)) {
                    $view = $this->render('listevent.one-event.cmd-accept-enrolled');
                }
            } elseif ($event->isStarted() && !$event->isOver()) {
                $view = $this->render('listevent.one-event.cmd-started');
            } elseif ($event->isOver()) {
                $averageRating = $this->getViewAverageRating($event);
                $view = $this->render('listevent.one-event.cmd-over', compact('averageRating'));
            }

            return $view;
        }

        /**
         * Generate view of average rating for panel
         * @param Event $event
         * @return null|string
         */
        public function getViewAverageRating(Event $event): ?string
        {
            if ($event->isOver()) {
                $averageRating = $event->getAverageRating();
                return $this->render('listevent.one-event.cmd-average-rating', compact('averageRating', 'event'));
            }

            return null;
        }


        /**
         * @throws DatabaseErrorException
         * @throws Exception
         */
        private function getViewEventWindow(Event $event): string
        {
            if ($this->globalConfidentiality($event)) {
                $tabs = array();
                $this->extensions->where(fn(EventExtension $e) => $e->isActivated())
                    ->forEach(function (EventExtension $extension) use (&$tabs) {
                        $tabs[$extension->order] = array(
                            $extension->extensionName,
                            $extension->getContent()
                        );
                    });
                ksort($tabs);
                return $this->render('listevent.one-event.event-window', compact('tabs'));
            }
            return $this->render('listevent.one-event.window-not-auth');
        }

        /**
         * @throws Exception
         */
        private function getViewEventNumbPart(Event $event): string
        {
            $participantsNumber = $event->getNumbParticipants();
            return $this->render("listevent.one-event.item-part-number", compact('event', 'participantsNumber'));
        }

        /**
         * Change registration of user for event
         * @param int $eventId
         * @return Response
         */
        public function subscribeToEvent(int $eventId): Response
        {
            try {
                $user = $this->authenticationGateway->getConnectedUserOrThrow();

                $this->eventService->changeRegistrationOfUSerToEvent($user->getID(), $eventId);

                return $this->ok();
            } catch (ResourceNotFound $exception) {
                $this->logger->logWarning($exception->getMessage());
                return $this->notFound();
            } catch (Exception $exception) {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }

        /**
         * @throws DatabaseErrorException
         * @throws EventNotExistException
         * @throws Exception
         */
        public function computeActionResponseForEvent(string $action, string $eventId): Response
        {
            $event = new Event((int)$eventId);
            switch ($action) {
                case "update.cmd":
                    $view = $this->getViewRegistrationCmd($event);
                    return new OkResponse($view);
                case "update.window":
                    $view = $this->getViewEventWindow($event);
                    return new OkResponse($view);
                case "update.partitem":
                    $view = $this->getViewEventNumbPart($event);
                    return new OkResponse($view);
            }
            return $this->computeResponseFromEventExtensions($action);
        }

        private function computeResponseFromEventExtensions(string $action): Response
        {
            $actionElements = explode('.', $action);
            $extractedExtensionId = $actionElements[0];
            foreach ($this->extensions as $extension) {
                /** @var $extension EventExtension */
                if ($extractedExtensionId != $extension->extensionId) {
                    continue;
                }
                $extensionAction = implode('.', array_slice($actionElements, 1));
                return $extension->computeActionQuery($extensionAction);
            }
            return new NotFoundResponse();
        }
    }
}
