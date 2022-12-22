<?php


namespace Business\Ports
{


    use Business\Entities\Event;
    use Business\Entities\NewEvent;
    use Business\Entities\SavedEvent;
    use Business\Exceptions\DatabaseErrorException;
    use PhpLinq\Interfaces\ILinq;

    interface EventRepositoryInterface
    {
        /**
         * Get Event from id if exist, null else
         * @param string $eventId id of event
         * @return Event|null event
         */
        public function getEvent(string $eventId): ?Event;

        /**
         * Get events for user
         * @param int $userId
         * @param int|null $cat
         * @param int|null $date
         * @return ILinq<Event>
         * @throws DatabaseErrorException
         */
        public function searchEventsForUser(int $userId, ?int $cat = null, ?int $date = null): ILinq;

        public function add(NewEvent $event);

        /**
         * @return ILinq<SavedEvent>
         */
        public function all(): ILinq;
    }
}
