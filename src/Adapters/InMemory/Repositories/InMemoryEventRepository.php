<?php

namespace Adapters\InMemory\Repositories;

use Business\Entities\Event;
use Business\Entities\NewEvent;
use Business\Entities\SavedEvent;
use Business\Exceptions\ValidationException;
use Business\Ports\EventRepositoryInterface;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;
use Tests\Utils\Builders\EventBuilder;

class InMemoryEventRepository implements EventRepositoryInterface
{
    public PhpLinq $events;
    private int $ids = 0;

    public function __construct()
    {
        $this->events = new PhpLinq();
    }

    public function getEvent(string $eventId): ?Event
    {
        // TODO: Implement getEvent() method.
    }

    public function searchEventsForUser(int $userId, ?int $cat = null, ?int $date = null): ILinq
    {
        // TODO: Implement searchEventsForUser() method.
    }

    /**
     * @throws ValidationException
     */
    public function haveAlreadyEvent(?callable $event = null): SavedEvent
    {
        $eventBuilder = EventBuilder::given();
        if ($event) {
            $event($eventBuilder);
        }
        $eventToSave = $eventBuilder->create();
        $this->events->add($eventToSave);
        $this->ids++;
        return $eventToSave;
    }

    /**
     * @throws ValidationException
     */
    public function add(NewEvent $event): SavedEvent
    {
        $nextId = $this->ids++;
        $savedEvent = new SavedEvent("$nextId", $event);
        $this->events->add($savedEvent);
        return $savedEvent;
    }

    public function all(): ILinq
    {
        return $this->events;
    }
}
