<?php

namespace Business\Exceptions;

use Business\Entities\Event;
use Throwable;

/**
 * Class EventDeletedException
 * When an event have deleted
 * @package App\Exceptions
 */
class EventDeletedException extends \Exception
{

    private $event;

    /**
     * EventDeletedException constructor.
     * @param Event|null $event
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Event $event = null, string $message = "L'évènement a été supprimé", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->event = $event;
    }

    /**
     * Get the event of exception
     * @return Event
     */
    public function getEvent(): Event {
        return $this->event;
    }

}