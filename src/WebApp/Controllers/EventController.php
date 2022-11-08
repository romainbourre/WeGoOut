<?php

namespace WebApp\Controllers
{


    use Business\Entities\Event;
    use Business\Ports\AuthenticationContextInterface;
    use Business\Services\EventService\IEventService;
    use Business\Services\EventService\Requests\SearchEventsRequest;
    use Business\ValueObjects\Location;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Configuration\IConfiguration;
    use System\Logging\ILogger;

    class EventController extends AppController
    {

        public function __construct(
            private readonly IConfiguration $configuration,
            private readonly ILogger $logger,
            private readonly IEventService $eventService,
            private readonly AuthenticationContextInterface $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * Generate global view of list of event or view of one event
         * @return Response
         */
        public function getView(): Response
        {
            try {
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();

                // WEB PAGE NAME
                $applicationName = $this->configuration['Application:Name'];
                $titleWebPage = "$applicationName - Évènements";

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

                $searchEventsRequest = new SearchEventsRequest();
                $list = $this->eventService->searchEventsForUser($connectedUser->getID(), $searchEventsRequest);

                $location = $connectedUser->getLocation();
                $contentEvents = $this->render('listevent.view-events', compact('list', 'location', 'connectedUser'));
                $categories = Event::getAllCategory();

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
                $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
                $params = $request->getParsedBody();

                $kilometersRadius = isset($params['dist']) ? (int)$params['dist'] : null;
                $categoryId = isset($params['cat']) && !empty($params['cat']) ? (int)$params['cat'] : null;
                $latitude = isset($params['lat']) && !empty($params['lat']) ? (float)$params['lat'] : null;
                $longitude = isset($params['lng']) && !empty($params['lng']) ? (float)$params['lng'] : null;

                $location = is_null($latitude) || is_null($longitude) ? $connectedUser->getLocation() : new Location(
                    $latitude,
                    $longitude
                );

                if (isset($params['date']) && !empty($params['date'])) {
                    $temp = $params['date'];

                    if (preg_match('#^([0-9]{2})([/-])([0-9]{2})\2([0-9]{4})$#', $temp, $d) && checkdate(
                            $d[3],
                            $d[1],
                            $d[4]
                        )) {
                        $temp = mktime(0, 0, 0, $d[3], $d[1], $d[4]);
                        $today = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));

                        if ($temp >= $today) {
                            $date = $temp;
                        } else {
                            $date = null;
                        }
                    } else {
                        $date = null;
                    }
                } else {
                    $date = null;
                }

                $searchEventsRequest = new SearchEventsRequest(
                    $kilometersRadius,
                    $latitude,
                    $longitude,
                    $categoryId,
                    $date
                );
                $list = $this->eventService->searchEventsForUser($connectedUser->getID(), $searchEventsRequest);

                $view = $this->render('listevent.view-events', compact('list', 'location', 'connectedUser'));

                return $this->ok($view);
            } catch (Exception $exception) {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }
    }
}