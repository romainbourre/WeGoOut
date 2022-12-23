<?php

namespace Adapters\InMemory\Repositories;

use Business\Entities\Event;
use Business\Entities\NewEvent;
use Business\Entities\SavedEvent;
use Business\Exceptions\ValidationException;
use Business\Ports\EventRepositoryInterface;
use DateTime;
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

    public function getSortedEventsByStartDateFromDate(DateTime $fromDate): ILinq
    {
        return $this->events
            ->where(fn(SavedEvent $event) => $event->dateRange->startAt >= $fromDate)
            ->orderBy(fn(SavedEvent $event) => $event->dateRange->startAt);
    }
}
