<?php


namespace Business\Services\EventService;


use Business\Entities\Event;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\ResourceNotFound;
use Business\Exceptions\UserNotExistException;
use Business\Services\EventService\Requests\SearchEventsRequest;

interface IEventService
{
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
     * @throws DatabaseErrorException
     */
    public function searchEventsForUser(int $userId, SearchEventsRequest $searchEventsRequest): array;

}
