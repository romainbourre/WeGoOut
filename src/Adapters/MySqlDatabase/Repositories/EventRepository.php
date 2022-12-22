<?php


namespace Adapters\MySqlDatabase\Repositories {

    use Business\Entities\Event;
    use Business\Entities\EventCategory;
    use Business\Entities\EventOwner;
    use Business\Entities\EventVisibilities;
    use Business\Entities\NewEvent;
    use Business\Entities\SavedEvent;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\EventNotExistException;
    use Business\Exceptions\ValidationException;
    use Business\Ports\EventRepositoryInterface;
    use Business\ValueObjects\EventDateRange;
    use Business\ValueObjects\EventLocation;
    use DateTime;
    use Exception;
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
         * Make PDO array errors to string
         * @param array $pdoError
         * @return string
         */
        private static function mapPDOErrorToString(array $pdoError): string
        {
            $errorString = '';
            foreach ($pdoError as $error) {
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

        /**
         * @throws DatabaseErrorException|ValidationException
         * @throws Exception
         */
        public function all(): ILinq
        {
            $query = $this->databaseContext->prepare(<<<'EOF'
SELECT 
    EVENT_ID as id,
    MUC.USER_ID as ownerId,
    MUC.CLI_FIRSTNAME as ownerFirstname,
    MUC.CLI_LASTNAME as ownerLastname,
    EVENT_CIRCLE as visibility,
    EVENT_TITLE as title,
    EVENT_DESCRIPTION as description,
    C.CAT_ID as categoryId,
    C.CAT_NAME as categoryName,
    EVENT_DATETIME_BEGIN as startAt,
    EVENT_DATETIME_END as endAt,
    EVENT_PARTICIPANTS_NUMBER as participantsLimit,
    EVENT_GUEST_ONLY as isGuestsOnly,
    EVENT_LOCATION_LABEL as address,
    EVENT_LOCATION_COMPLEMENTS as addressDetails,
    EVENT_LOCATION_CP as postalCode,
    EVENT_LOCATION_CITY as city,
    EVENT_LOCATION_COUNTRY as country,
    EVENT_LOCATION_LNG as longitude,
    EVENT_LOCATION_LAT as latitude
FROM EVENT
    JOIN META_USER_CLI MUC on EVENT.USER_ID = MUC.USER_ID
    JOIN CATEGORY C on C.CAT_ID = EVENT.CAT_ID
EOF
            );
            if (!$query->execute()) {
                $errorMessage = self::mapPDOErrorToString($query->errorInfo());
                throw new DatabaseErrorException($errorMessage);
            }
            $events = new PhpLinq();
            while ($result = $query->fetch()) {
                $event = new NewEvent(
                    visibility: $result['visibility'] == 1 ? EventVisibilities::PUBLIC : EventVisibilities::PRIVATE,
                    owner: new EventOwner($result['ownerId'], $result['ownerFirstname'], $result['ownerLastname']),
                    title: $result['title'],
                    category: new EventCategory($result['categoryId'], $result['categoryName']),
                    dateRange: new EventDateRange(
                        startAt: new DateTime($result['startAt']),
                        endAt: isset($result['endAt']) ? new DateTime($result['endAt']) : null
                    ),
                    description: $result['description'],
                    participantsLimit: $result['participantsLimit'],
                    isGuestsOnly: $result['isGuestsOnly'] == 1,
                    location: new EventLocation(
                        address: $result['address'],
                        postalCode: $result['postalCode'],
                        city: $result['city'],
                        country: $result['country'],
                        addressDetails: $result['addressDetails'],
                        latitude: $result['latitude'],
                        longitude: $result['longitude'],
                    )
                );
                $savedEvent = new SavedEvent(id: $result['id'], event: $event);
                $events->add($savedEvent);
            }
            return $events;
        }
    }
}
