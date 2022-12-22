<?php


namespace Business\Services\EventService;


use Business\Entities\Event;
use Business\Exceptions\ResourceNotFound;

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
}
