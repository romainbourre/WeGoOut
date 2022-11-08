<?php

namespace WebApp\Controllers
{


    use Business\Entities\Alert;
    use Business\Exceptions\BadArgumentException;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\UserHadAlreadyEventsException;
    use Business\Ports\AuthenticationContextInterface;
    use Business\Services\EventService\IEventService;
    use Business\Services\EventService\Requests\CreateEventRequest;
    use DateTime;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Logging\ILogger;
    use WebApp\Librairies\AppSettings;

    class  CreateEventController extends AppController
    {

        public function __construct(
            private readonly ILogger $logger,
            private readonly IEventService $eventService,
            private readonly AuthenticationContextInterface $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * Load view of web page
         */
        public function getView(): void
        {
            $this->addJsScript('create-view.js');
            $this->addJsScript('create-date.js');
            $this->addJsScript('create-ctrl.js');
            $this->addJsScript('create-location.js');
        }

        /**
         * Get view of form to create new event
         * @return Response
         */
        public function getCreateEventForm(): Response
        {
            try {
                $eventCategories = $this->eventService->getCategories()->toArray();
                $content = $this->render('create.view-create', compact('eventCategories'));

                return $this->ok($content);
            } catch (Exception $exception) {
                $this->logger->logCritical($exception->getMessage(), $exception);
                return $this->internalServerError();
            }
        }

        /**
         * Create a new event for user
         * @param Request $request
         * @return Response
         */
        public function createEvent(Request $request): Response
        {
            try {
                if (is_null($cleaned_data = $this->getData($request))) {
                    return $this->badRequest();
                }

                $user = $this->authenticationGateway->getConnectedUserOrThrow();

                list(
                    $event_circle,
                    $event_title,
                    $event_category,
                    $event_description,
                    $event_guest_only,
                    $event_number_participants,
                    $event_datetime_begin,
                    $event_datetime_end,
                    $event_location,
                    $event_location_complements,
                    $event_place_id,
                    $event_address,
                    $event_cp,
                    $event_city,
                    $event_country,
                    $event_lat,
                    $event_lng
                    ) = $cleaned_data;

                $userId = $user->getID();
                $event_datetime_begin = (new DateTime())->setTimestamp($event_datetime_begin);

                if (!is_null($event_datetime_end)) {
                    $event_datetime_end = (new DateTime())->setTimestamp($event_datetime_end);
                }

                $createEventRequest = new CreateEventRequest(
                    $event_title, $event_circle, $event_category,
                    $event_description, $event_guest_only, $event_number_participants, $event_datetime_begin,
                    $event_datetime_end, $event_location, $event_location_complements, $event_place_id,
                    $event_address, $event_cp, $event_city, $event_country, $event_lat, $event_lng
                );

                $this->eventService->createEvent($userId, $createEventRequest);
                $this->logger->logTrace("event created.");

                return $this->ok();
            } catch (BadArgumentException|UserHadAlreadyEventsException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert("Impossible de créer l'évennement : {$e->getMessage()}", 3);
                echo "<script>document.getElementById('form_group_create').className += ' has-danger'</script>";
                echo "<script>document.getElementById('create_feedback').innerHTML = 'Des champs semblent incorrects'</script>";
                return $this->badRequest();
            } catch (Exception|DatabaseErrorException $e) {
                $this->logger->logCritical($e->getMessage());
                return $this->internalServerError();
            }
        }

        /**
         * Recover data of form web page and check (level 1)
         * @return array|null cleaned data or false
         */
        private function getData(Request $request): ?array
        {
            $run = true;
            $params = $request->getParsedBody();

            if (

                isset($params['create-event-circle-switch'])
                && isset($params['create-event-title-field'])
                && isset($params['create-event-category-select'])
                && isset($params['create-event-location-field'])
                && isset($params['create-event-place-hidden'])
                && isset($params['create-event-latitude-hidden'])
                && isset($params['create-event-longitude-hidden'])

            ) {
                $event_circle = (int)htmlspecialchars($params['create-event-circle-switch'] ?? 2);
                $event_title = htmlspecialchars($params['create-event-title-field']);
                $event_category = (int)htmlspecialchars($params['create-event-category-select']);
                $event_description = htmlspecialchars($params['create-event-desc-text'] ?? '');
                $event_guest_only = (int)htmlspecialchars($params['create-event-guest-check'] ?? 0);
                $event_number_participants = (int)htmlspecialchars(
                    $params['create-event-participants-number'] ?? (new AppSettings())->getParticipantMinNumber()
                );
                $event_date_begin = htmlspecialchars($params['create-event-dateBegin-field'] ?? date("d/m/Y"));
                $event_hour_begin = htmlspecialchars($params['create-event-timeBegin-field'] ?? date("H:i"));
                $event_date_end_active = htmlspecialchars($params['create-event-datetimeEnd-active'] ?? "false");
                $event_date_end = htmlspecialchars($params['create-event-dateEnd-field']);
                $event_hour_end = htmlspecialchars($params['create-event-timeEnd-field']);
                $event_location = htmlspecialchars($params['create-event-location-field']);
                $event_location_complements = htmlspecialchars($params['create-event-compAddress-text']);
                $event_place_id = htmlspecialchars($params['create-event-place-hidden']);
                $event_address = htmlspecialchars($params['create-event-address-hidden']);
                $event_cp = htmlspecialchars($params['create-event-postal-hidden']);
                $event_city = htmlspecialchars($params['create-event-city-hidden']);
                $event_country = htmlspecialchars($params['create-event-country-hidden']);
                $event_lat = (double)htmlspecialchars($params['create-event-latitude-hidden']);
                $event_lng = (double)htmlspecialchars($params['create-event-longitude-hidden']);

                //CHECK DATES AND HOURS
                if (preg_match('#^([0-9]{2})([/-])([0-9]{2})\2([0-9]{4})$#', $event_date_begin, $d) && checkdate(
                        $d[3],
                        $d[1],
                        $d[4]
                    )) { // BEGIN DATE
                    if (preg_match(
                            '#([0-9]{2})(:)([0-9]{2})#',
                            $event_hour_begin,
                            $h
                        ) && $h[1] >= 0 && $h[1] < 24 && $h[3] >= 0 && $h[3] < 60) { // BEGIN HOUR

                        $event_datetime_begin = mktime($h[1], $h[3], 0, $d[3], $d[1], $d[4]);
                    } else {
                        $event_datetime_begin = time() + (3600 * 1000);
                    }
                } else {
                    $event_datetime_begin = time() + (3600 * 1000);
                }

                if ($event_date_end_active == "true") {
                    if (preg_match('#^([0-9]{2})([/-])([0-9]{2})\2([0-9]{4})$#', $event_date_end, $d) && checkdate(
                            $d[3],
                            $d[1],
                            $d[4]
                        )) { // END DATE
                        if (preg_match(
                                '#([0-9]{2})(:)([0-9]{2})#',
                                $event_hour_end,
                                $h
                            ) && $h[1] >= 0 && $h[1] < 24 && $h[3] >= 0 && $h[3] < 60) { // END HOUR

                            $event_datetime_end = mktime($h[1], $h[3], 0, $d[3], $d[1], $d[4]);
                        } else {
                            $event_datetime_end = $event_datetime_begin + (3600 * 1000);
                        }
                    } else {
                        $event_datetime_end = $event_datetime_begin + (3600 * 1000);
                    }
                } else {
                    $event_datetime_end = null;
                }

                // CHECK LOCATION
                if (empty($event_location) && empty($event_place_id) && empty($event_lat) && empty($event_lng)) {
                    $run = false;
                }

                // IF NO ERROR RETURN CLEANED DATA
                if ($run) {
                    return array(
                        $event_circle,
                        $event_title,
                        $event_category,
                        $event_description,
                        $event_guest_only,
                        $event_number_participants,
                        $event_datetime_begin,
                        $event_datetime_end,
                        $event_location,
                        $event_location_complements,
                        $event_place_id,
                        $event_address,
                        $event_cp,
                        $event_city,
                        $event_country,
                        $event_lat,
                        $event_lng,
                    );
                }
            } else {
                Alert::addAlert("Veuillez remplir tous les champs", 3);
                echo "<script>document.getElementById('form_group_create').className += ' has-danger'</script>";
                echo "<script>document.getElementById('create_feedback').innerHTML = 'Veuillez remplir tous les champs'</script>";
            }

            return null;
        }

    }
}