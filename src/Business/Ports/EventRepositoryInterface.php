<?php


namespace Business\Ports
{


    use Business\Entities\Event;
    use Business\Exceptions\DatabaseErrorException;
    use DateTime;
    use PhpLinq\Interfaces\ILinq;

    interface EventRepositoryInterface
    {
        /**
         * Save a new event
         * @param array $cleaned_data
         * @throws DatabaseErrorException
         */
        public function saveEvent(array $cleaned_data): void;

        /**
         * Get Event from id if exist, null else
         * @param string $eventId id of event
         * @return Event|null event
         */
        public function getEvent(string $eventId): ?Event;

        /**
         * @param int $userId
         * @param DateTime $datetimeBegin
         * @param DateTime|null $datetimeEnd
         * @return int
         */
        public function findUserEventsNumberForDates(int $userId, DateTime $datetimeBegin, ?DateTime $datetimeEnd): int;

        /**
         * Get events for user
         * @param int $userId
         * @param int|null $cat
         * @param int|null $date
         * @return ILinq<Event>
         *@throws DatabaseErrorException
         */
        public function searchEventsForUser(int $userId, ?int $cat = null, ?int $date = null): ILinq;
    }
}