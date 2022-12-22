<?php

namespace WebApp\Controllers
{


    use Business\Entities\Event;
    use Business\Exceptions\EventNotExistException;
    use Business\Exceptions\NonConnectedUserException;
    use Business\Ports\EmailSenderInterface;
    use Business\Ports\EventCategoryRepositoryInterface;
    use Business\Services\EventService\IEventService;
    use Business\UseCases\SearchEvent\Response\SearchEventsWithCriteriaResponse;
    use Business\UseCases\SearchEvent\SearchEventsWithCriteriaRequest;
    use Business\UseCases\SearchEvent\SearchEventsWithCriteriaUseCase;
    use DateTime;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Configuration\ConfigurationInterface;
    use System\Logging\LoggerInterface;
    use WebApp\Attributes\Page;
    use WebApp\Authentication\AuthenticationContext;
    use WebApp\Exceptions\NotConnectedUserException;
    use WebApp\Routing\ParametersRequestExtractor;
    use WebApp\Services\ToasterService\ToasterInterface;

    class EventController extends AppController
    {

        public function __construct(
            private readonly ConfigurationInterface           $configuration,
            private readonly LoggerInterface                  $logger,
            private readonly IEventService                    $eventService,
            private readonly AuthenticationContext            $authenticationGateway,
            private readonly EmailSenderInterface             $emailSender,
            private readonly ToasterInterface                 $toaster,
            private readonly EventCategoryRepositoryInterface $categoryRepository,
            private readonly SearchEventsWithCriteriaUseCase  $searchEventWithCriteriaUseCase,
        )
        {
            parent::__construct();
        }

        #[Page("events.css", "events.js")]
        public function getView(Request $request): Response
        {
            try {
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();

                // WEB PAGE NAME
                $applicationName = $this->configuration['Application:Name'];
                $titleWebPage = "$applicationName - Évènements";

                // NAVIGATION
                $userItems = $this->render('templates.nav-useritems');
                $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));
                $contentEvents = $this->getEventListView($request);
                $categories = $this->categoryRepository->all();
                $content = $this->render('listevent.view-listevent', compact('categories', 'contentEvents'));
                $view = $this->render(
                    'templates.template',
                    compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content', 'connectedUser')
                );

                return $this->ok($view);
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage(), $e);
                return $this->internalServerError();
            }
        }

        /**
         * Generate view of events list
         * @param Request $request request
         * @return Response view of events list
         */
        public function searchEvents(Request $request): Response
        {
            try {
                $view = $this->getEventListView($request);
                return $this->ok($view);
            } catch (Exception $exception) {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }

        /**
         * @throws NotConnectedUserException
         * @throws NonConnectedUserException
         * @throws Exception
         */
        private function getEventListView(Request $request): string
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $searchEventsRequest = $this->extractSearchEventRequest($request);
            $researchEventsResult = $this->searchEventWithCriteriaUseCase->handle($searchEventsRequest);
            $list = $this->extractEventsAndGroupByDate($researchEventsResult);
            return $this->render('listevent.view-events', compact('list', 'connectedUser'));
        }

        private function extractSearchEventRequest(Request $request): SearchEventsWithCriteriaRequest
        {
            $params = new ParametersRequestExtractor($request);
            return new SearchEventsWithCriteriaRequest(
                latitude: $params->get('lat')->asFloat(),
                longitude: $params->get('lng')->asFloat(),
                categoryId: $params->get('cat')->asInt(),
                distance: $params->get('dist')->asInt(),
                from: $params->get('date')->asDatetime(),
            );
        }

        private function extractEventsAndGroupByDate(SearchEventsWithCriteriaResponse $researchResponse): array
        {
            $eventsByDate = [];
            foreach ($researchResponse->events as $event) {
                $startDay = new DateTime();
                $startDay->setTimestamp($event->startAt->getTimestamp());
                $startDay->setTime(0, 0);
                $eventsByDate[$startDay->getTimestamp()][] = $event;
            }
            return $eventsByDate;
        }

        /**
         * @throws EventNotExistException
         */
        public function forEvent(int $eventId): OneEventController
        {
            $event = new Event($eventId);
            return new OneEventController($this->logger, $this->eventService, $this->authenticationGateway, $this->emailSender, $this->toaster, $event);
        }
    }
}
