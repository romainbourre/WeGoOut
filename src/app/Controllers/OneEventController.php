<?php


namespace App\Controllers
{


    use App\Authentication\AuthenticationContext;
    use App\Controllers\EventExtensions\Extensions\TabAbout;
    use App\Controllers\EventExtensions\Extensions\TabParticipants;
    use App\Controllers\EventExtensions\Extensions\TabPublications;
    use App\Controllers\EventExtensions\Extensions\TabReviews;
    use App\Controllers\EventExtensions\Extensions\TabToDoList;
    use App\Controllers\EventExtensions\IEventExtension;
    use Domain\Entities\Event;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Services\EventService\IEventService;
    use Exception;
    use PhpLinq\PhpLinq;
    use System\Logging\ILogger;
    use Slim\Psr7\Response;

    /**
     * Class OneEvent
     * Represent page of an event
     * @package App\Controllers
     * @author Bourré Romain
     */
    class OneEventController extends AppController
    {
        /**
         * @var array<string>
         */
        private const extensions = [
            TabAbout::class,
            TabParticipants::class,
            TabPublications::class,
            TabReviews::class,
            TabToDoList::class
        ];

        public function __construct(
            private readonly ILogger $logger,
            private readonly IEventService $eventService,
            private readonly AuthenticationContext $authenticationGateway
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
                        compact('event', 'userLocation', 'registrationCmd', 'numbPartItem', 'contentWindow', 'connectedUser')
                    );
                } else {
                    $content = $this->render('listevent.one-event.event-not-auth');
                }

                $view = $this->render(
                    'templates.template',
                    compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content', 'connectedUser')
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
         * Generate view of registration and edit command
         * @param Event $event
         * @return string|null
         * @throws Exception
         */
        private function getViewRegistrationCmd(Event $event): ?string
        {
            $currentUser = $this->authenticationGateway->getConnectedUser();
            if (!$event->isStarted() && !$event->isOver()) {
                if ($event->isCreator($currentUser) || $event->isOrganizer($currentUser)) {
                    return $this->render('listevent.one-event.cmd-edit', compact('event'));
                } elseif ($event->isParticipantWait($currentUser)) {
                    return $this->render('listevent.one-event.cmd-wait-enrolled');
                } elseif (!$event->isParticipant($currentUser)) {
                    return $this->render("listevent.one-event.cmd-not-enrolled");
                } elseif ($event->isParticipantValid($currentUser)) {
                    return $this->render('listevent.one-event.cmd-accept-enrolled');
                }
            } elseif ($event->isStarted() && !$event->isOver()) {
                return $this->render('listevent.one-event.cmd-started');
            } elseif ($event->isOver()) {
                $averageRating = $this->getViewAverageRating($event);
                return $this->render('listevent.one-event.cmd-over', compact('averageRating'));
            }

            return null;
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
         * Generate view of event window
         * @param Event $event
         * @return string view of event window
         */
        private function getViewEventWindow(Event $event): string
        {
            $extensionsClass = PhpLinq::fromArray(self::extensions);
            $extensions = $extensionsClass
                ->select(fn(string $class) => new $class($this->authenticationGateway, $event, $this->logger, $this->eventService))
                ->where(fn(IEventExtension $extension) => $extension->isActivated());

            if ($this->globalConfidentiality($event)) {
                $tabs = array();

                $extensions->forEach(function (IEventExtension $extension) use (&$tabs)
                {
                    $tabs[$extension->getTabPosition()] = array(
                        $extension->getExtensionName(),
                        $extension->getContent()
                    );
                });

                ksort($tabs);

                return $this->render('listevent.one-event.event-window', compact('tabs'));
            }

            return $this->render('listevent.one-event.window-not-auth');
        }

        /**
         * Generate view of number of participants item for panel
         * @param Event $event
         * @return string view of panel participants
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
         * Generate view from ajax request
         * @param $action string action to be taken
         * @return null|string view requested
         * @throws Exception
         */
        public function getAjaxEventView(string $action, string $eventId): ?string
        {
            $event = new Event((int)$eventId);
            $extensionsClass = PhpLinq::fromArray(self::extensions);
            $extensions = $extensionsClass->select(
                fn(string $class) => new $class($this->authenticationGateway, $event, $this->logger, $this->eventService)
            );

            switch ($action) {
                case "update.cmd":
                    return $this->getViewRegistrationCmd($event);
                case "update.window":
                    return $this->getViewEventWindow($event);
                case "update.partitem":
                    return $this->getViewEventNumbPart($event);
            }

            $extensions->forEach(fn(IEventExtension $extension) => $extension->getAjaxSwitch($action));

            return null;
        }
    }
}