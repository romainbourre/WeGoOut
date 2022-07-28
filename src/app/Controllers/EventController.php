<?php

namespace App\Controllers
{


    use Domain\Entities\Event;
    use Domain\Entities\Location;
    use Domain\Services\EventService\IEventService;
    use Domain\Services\EventService\Requests\SearchEventsRequest;
    use Exception;
    use Slim\Psr7\Request;
    use System\Configuration\IConfiguration;
    use System\Logging\ILogger;
    use Slim\Psr7\Response;

    class EventController extends AppController
    {
        private IConfiguration $configuration;
        private ILogger       $logger;
        private IEventService $eventService;

        /**
         * EventController constructor.
         * @param IConfiguration $configuration
         * @param ILogger $logger
         * @param IEventService $eventService
         */
        public function __construct(IConfiguration $configuration, ILogger $logger, IEventService $eventService)
        {
            parent::__construct();
            $this->configuration = $configuration;
            $this->logger = $logger;
            $this->eventService = $eventService;
        }

        /**
         * Generate global view of list of event or view of one event
         * @return Response
         */
        public function getView(): Response
        {
            try
            {
                $user = $this->getConnectedUserOrThrow();

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
                $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu'));
                $navAddEvent = $this->render('templates.nav-addevent');
                $navItems = $this->render('templates.nav-connectmenu', compact('navAddEvent'));

                $searchEventsRequest = new SearchEventsRequest();
                $list = $this->eventService->searchEventsForUser($user->getID(), $searchEventsRequest);

                $location = $user->getLocation();
                $contentEvents = $this->render('listevent.view-events', compact('list', 'location'));
                $categories = Event::getAllCategory();

                $content = $this->render('listevent.view-listevent', compact('categories', 'contentEvents'));

                $view = $this->render('templates.template', compact('titleWebPage', 'userMenu', 'navUserDropDown', 'navAddEvent', 'navItems', 'content'));

                return $this->ok($view);

            }
            catch (Exception $e)
            {
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
            try
            {
                $user = $this->getConnectedUserOrThrow();
                $params = $request->getParsedBody();

                $kilometersRadius = isset($params['dist']) ? (int)$params['dist'] : null;
                $categoryId = isset($params['cat']) && !empty($params['cat']) ? (int)$params['cat'] : null;
                $latitude = isset($params['lat']) && !empty($params['lat']) ? (float)$params['lat'] : null;
                $longitude = isset($params['lng']) && !empty($params['lng']) ? (float)$params['lng'] : null;

                $location = is_null($latitude) || is_null($longitude) ? $user->getLocation() : new Location($latitude, $longitude);

                if (isset($params['date']) && !empty($params['date']))
                {
                    $temp = $params['date'];

                    if (preg_match('#^([0-9]{2})([/-])([0-9]{2})\2([0-9]{4})$#', $temp, $d) && checkdate($d[3], $d[1], $d[4]))
                    {
                        $temp = mktime(0, 0, 0, $d[3], $d[1], $d[4]);
                        $today = mktime(0, 0, 0, date('m', time()), date('d', time()), date('Y', time()));

                        if ($temp >= $today)
                        {
                            $date = $temp;
                        }
                        else
                        {
                            $date = null;
                        }

                    }
                    else
                    {
                        $date = null;
                    }

                }
                else
                {
                    $date = null;
                }

                $searchEventsRequest = new SearchEventsRequest($kilometersRadius, $latitude, $longitude, $categoryId, $date);
                $list = $this->eventService->searchEventsForUser($user->getID(), $searchEventsRequest);

                $view = $this->render('listevent.view-events', compact('list', 'location'));
                
                return $this->ok($view);

            }
            catch (Exception $exception)
            {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }
    }
}