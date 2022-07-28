<?php


namespace Domain\Services\EventService
{


    use Domain\Entities\Event;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\DataNotSavedException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Exceptions\UserHadAlreadyEventsException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Services\EventService\Requests\CreateEventRequest;
    use Domain\Services\EventService\Requests\SearchEventsRequest;
    use PhpLinq\Interfaces\ILinq;

    interface IEventService
    {
        /**
         * Create an event for user
         * @param int $userId
         * @param CreateEventRequest $createEventRequest
         * @throws BadArgumentException
         * @throws DataNotSavedException
         * @throws UserHadAlreadyEventsException
         */
        public function createEvent(int $userId, CreateEventRequest $createEventRequest): void;

        /**
         * Get categories available for event
         * @return ILinq<string> list of categories
         */
        public function getCategories(): ILinq;

        /**
         * Get event from id
         * @param string $eventId id of event
         * @return Event
         * @throws ResourceNotFound
         */
        public function getEvent(string $eventId): Event;

        /**
         * Register user to register to event
         * @param int $userId id of user
         * @param int $eventId id of event
         * @throws ResourceNotFound if event not found
         */
        public function changeRegistrationOfUSerToEvent(int $userId, int $eventId): void;

        /**
         * Search events for user corresponding to arguments
         * @param int $userId user id
         * @param SearchEventsRequest $searchEventsRequest arguments of research request
         * @return Event[] list of events for user
         * @throws UserNotExistException
         * @throws DataNotSavedException
         */
        public function searchEventsForUser(int $userId, SearchEventsRequest $searchEventsRequest): array;

    }
}