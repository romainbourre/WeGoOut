<?php


namespace Infrastructure\MySqlDatabase\Repositories
{

    use DateTime;
    use Domain\Entities\Event;
    use Domain\Entities\Location;
    use Domain\Exceptions\DataNotSavedException;
    use Domain\Exceptions\EventCanceledException;
    use Domain\Exceptions\EventDeletedException;
    use Domain\Exceptions\EventNotExistException;
    use Domain\Exceptions\EventSignaledException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Interfaces\IEventRepository;
    use PDO;
    use PhpLinq\Interfaces\ILinq;
    use PhpLinq\PhpLinq;

    class EventRepository implements IEventRepository
    {
        private PDO $databaseContext;

        /**
         * EventRepository constructor.
         * @param PDO $databaseContext
         */
        public function __construct(PDO $databaseContext)
        {
            $this->databaseContext = $databaseContext;
        }

        /**
         * Save a new event
         * @param array $cleaned_data
         * @throws DataNotSavedException
         */
        public function saveEvent(array $cleaned_data): void
        {

            $bdd = $this->databaseContext;
            list($user_id,
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
                $event_lng) = $cleaned_data;

            $statement = "INSERT INTO EVENT(
                                      USER_ID,
                                      CAT_ID,
                                      EVENT_TITLE, 
                                      EVENT_DESCRIPTION, 
                                      EVENT_LOCATION_LABEL, 
                                      EVENT_LOCATION_COMPLEMENTS,
                                      EVENT_LOCATION_ADDRESS, 
                                      EVENT_LOCATION_CP,
                                      EVENT_LOCATION_CITY,
                                      EVENT_LOCATION_COUNTRY,
                                      EVENT_LOCATION_PLACE_ID, 
                                      EVENT_LOCATION_LNG, 
                                      EVENT_LOCATION_LAT, 
                                      EVENT_DATETIME_BEGIN, 
                                      EVENT_DATETIME_END, 
                                      EVENT_CIRCLE,
                                      EVENT_PARTICIPANTS_NUMBER,  
                                      EVENT_GUEST_ONLY,
                                      EVENT_DATETIME_CREATE) 
                            VALUES (
                                :userId,
                                :cat,
                                :title,
                                :eventDesc,
                                :location,
                                :locationComp,
                                :address,
                                :cp,
                                :city,
                                :country,
                                :placeId,
                                :placeLng,
                                :placeLat,
                                :dateTimeBegin,
                                :dateTimeEnd,
                                :circle,
                                :nbrPart,
                                :guestOnly,
                                sysdate()
                            )";

            $request = $bdd->prepare($statement);
            $request->bindValue(':userId', $user_id);
            $request->bindValue(':cat', $event_category);
            $request->bindValue(':title', $event_title);
            $request->bindValue(':eventDesc', $event_description);
            $request->bindValue(':location', $event_location);
            $request->bindValue(':locationComp', $event_location_complements);
            $request->bindValue(':address', $event_address);
            $request->bindValue(':cp', $event_cp);
            $request->bindValue(':city', $event_city);
            $request->bindValue(':country', $event_country);
            $request->bindValue(':placeId', $event_place_id);
            $request->bindValue(':placeLng', $event_lng);
            $request->bindValue(':placeLat', $event_lat);
            $request->bindValue(':dateTimeBegin', $event_datetime_begin->format('Y-m-d H:i:s'));
            $request->bindValue(':dateTimeEnd', $event_datetime_end != null ? $event_datetime_begin->format('Y-m-d H:i:s') : null);
            $request->bindValue(':circle', $event_circle);
            $request->bindValue(':nbrPart', $event_number_participants);
            $request->bindValue(':guestOnly', $event_guest_only ? 1 : 0);

            if (!$request->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DataNotSavedException($errorMessage);
            }
        }

        /**
         * @inheritDoc
         */
        public function searchEventsForUser(int $userId, ?int $cat = null, ?int $date = null): ILinq
        {
            $events = new PhpLinq();

            if (!is_null($cat)) $requestInter = 'tab1.cat_id = :catId AND';
            else $requestInter = '';
            if (!is_null($date)) $requestDate = 'date(event_datetime_begin) = :dateEvent';
            else $requestDate = 'date(event_datetime_begin) >= date(sysdate())';

            $request = $this->databaseContext->prepare('SELECT distinct tab1.event_id, tab1.event_datetime_begin FROM EVENT tab1 LEFT JOIN USER USING(user_id) LEFT JOIN GUEST tab2 ON tab1.event_id = tab2.event_id LEFT JOIN FRIENDS tab3 ON tab1.user_id = tab3.user_id OR tab1.user_id = tab3.user_id_1 WHERE EVENT_DATETIME_CANCEL is null AND EVENT_DATETIME_DELETE is null AND EVENT_VALID = 1 AND USER_DATETIME_DELETE is null AND USER_VALID = 1 AND ' . $requestDate . ' AND ' . $requestInter . ' ( tab1.user_id = :userId OR tab1.event_circle = 1 OR ( tab1.event_circle = 2 AND (tab1.event_guest_only != 1 OR tab1.event_guest_only is null ) AND ( tab3.user_id = :userId OR tab3.user_id_1 = :userId ) AND tab3.fri_datetime_demand is not null AND tab3.fri_datetime_accept is not null AND tab3.fri_datetime_delete is null  ) OR ( tab1.event_circle = 2 AND tab1.event_guest_only = 1 AND tab2.user_id = :userId AND tab2.guest_datetime_send is not null AND tab2.guest_datetime_delete is null ) ) order by EVENT_DATETIME_BEGIN ASC');
            $request->bindValue(':dateEvent', date('Y-m-d', $date));
            $request->bindValue(':userId', $userId);
            $request->bindValue(':catId', $cat);

            if (!$request->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DataNotSavedException($errorMessage);
            }

            while ($resultEvent = $request->fetch())
            {
                try
                {
                    $eventId = $resultEvent['event_id'];
                    $event = new Event($eventId);
                    $events->add($event);
                }
                catch (EventNotExistException | EventDeletedException | EventCanceledException | EventSignaledException | UserNotExistException | UserDeletedException | UserSignaledException $e)
                {
                }

            }

            return $events;
        }

        /**
         * Find events where user is participant or organizer for dates
         * @param int $userId id of user
         * @param DateTime $datetimeBegin start of the event
         * @param DateTime|null $datetimeEnd end of the event (if exist)
         * @return int number of events found
         */
        public function findUserEventsNumberForDates(int $userId, DateTime $datetimeBegin, ?DateTime $datetimeEnd): int
        {

            $bdd = $this->databaseContext;

            $statement = 'SELECT sum(nbrLapEvent) as nbrLapEvent
                      FROM (
                        SELECT count(*) as nbrLapEvent FROM EVENT WHERE EVENT_DATETIME_BEGIN >= :dateTimeBegin AND EVENT_DATETIME_BEGIN <= :dateTimeEnd AND USER_ID = :userId 
                        UNION  
                        SELECT count(*) as nbrLapEvent FROM EVENT WHERE EVENT_DATETIME_END >= :dateTimeBegin AND EVENT_DATETIME_END <= :dateTimeEnd AND USER_ID = :userId
                      ) as tab';

            $request = $bdd->prepare($statement);

            $request->bindValue(':dateTimeBegin', $datetimeBegin->format('Y-m-d H:i:s'));
            $request->bindValue(':dateTimeEnd', $datetimeEnd != null ? $datetimeEnd->format('Y-m-d H:i:s') : null);
            $request->bindValue(':userId', $userId);

            if ($request->execute())
            {

                $result = $request->fetch();

                return $result['nbrLapEvent'];

            }

            return 0;
        }

        /**
         * Make PDO array errors to string
         * @param array $pdoError
         * @return string
         */
        private static function mapPDOErrorToString(array $pdoError): string
        {
            $errorString = '';
            foreach ($pdoError as $error)
            {
                $errorString .= "$error ";
            }

            return $errorString;
        }

        /**
         * @inheritDoc
         */
        public function getEvent(string $eventId): ?Event
        {
            try
            {
                return new Event((int)$eventId);
            }
            catch (EventNotExistException)
            {
                return null;
            }
        }
    }
}