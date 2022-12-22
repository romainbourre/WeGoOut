<?php


namespace Business\Ports
{


    use Business\Entities\Event;
    use Business\Entities\NewEvent;
    use Business\Entities\SavedEvent;
    use PhpLinq\Interfaces\ILinq;

    interface EventRepositoryInterface
    {
        /**
         * Get Event from id if exist, null else
         * @param string $eventId id of event
         * @return Event|null event
         */
        public function getEvent(string $eventId): ?Event;

        public function add(NewEvent $event);

        /**
         * @return ILinq<SavedEvent>
         */
        public function all(): ILinq;
    }
}
