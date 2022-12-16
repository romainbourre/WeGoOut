<?php


namespace Adapters\MySqlDatabase\Repositories
{

    use Business\Entities\Event;
    use Business\Entities\EventVisibilities;
    use Business\Entities\NewEvent;
    use Business\Entities\SavedEvent;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\EventCanceledException;
    use Business\Exceptions\EventDeletedException;
    use Business\Exceptions\EventNotExistException;
    use Business\Exceptions\EventSignaledException;
    use Business\Exceptions\UserDeletedException;
    use Business\Exceptions\UserNotExistException;
    use Business\Exceptions\UserSignaledException;
    use Business\Exceptions\ValidationException;
    use Business\Ports\EventRepositoryInterface;
    use PDO;
    use PhpLinq\Interfaces\ILinq;
    use PhpLinq\PhpLinq;

    class EventRepository implements EventRepositoryInterface
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
                throw new DatabaseErrorException($errorMessage);
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
            try {
                return new Event((int)$eventId);
            } catch (EventNotExistException) {
                return null;
            }
        }

        /**
         * @throws DatabaseErrorException
         * @throws ValidationException
         */
        public function add(NewEvent $event): SavedEvent
        {
            $bdd = $this->databaseContext;
            $statement = <<<EOF
INSERT INTO EVENT(
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
                            )
EOF;

            $visibility = match ($event->visibility) {
                EventVisibilities::PRIVATE => 2,
                default => 1,
            };
            $request = $bdd->prepare($statement);
            $request->bindValue(':userId', $event->owner->id);
            $request->bindValue(':cat', $event->category->id);
            $request->bindValue(':title', $event->title);
            $request->bindValue(':eventDesc', $event->description);
            $request->bindValue(':location', $event->location->address);
            $request->bindValue(':locationComp', $event->location->addressDetails);
            $request->bindValue(':address', '');
            $request->bindValue(':cp', $event->location->postalCode);
            $request->bindValue(':city', $event->location->city);
            $request->bindValue(':country', $event->location->country);
            $request->bindValue(':placeId', '');
            $request->bindValue(':placeLng', $event->location->longitude);
            $request->bindValue(':placeLat', $event->location->latitude);
            $request->bindValue(':dateTimeBegin', $event->dateRange->startAt->format('Y-m-d H:i:s'));
            $request->bindValue(':dateTimeEnd', $event->dateRange->endAt?->format('Y-m-d H:i:s'));
            $request->bindValue(':circle', $visibility);
            $request->bindValue(':nbrPart', $event->participantsLimit);
            $request->bindValue(':guestOnly', $event->isGuestsOnly ? 1 : 0);

            if (!$request->execute() || ($generatedId = $bdd->query('SELECT LAST_INSERT_ID()')->fetchColumn()) === false) {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DatabaseErrorException($errorMessage);
            }

            return new SavedEvent((int)$generatedId, $event);
        }
    }
}
