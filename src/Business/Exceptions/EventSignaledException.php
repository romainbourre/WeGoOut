<?php

namespace Business\Exceptions;

use Business\Entities\Event;
use Throwable;

/**
 * Class EventSignaledException
 * When an event have signaled
 * @package App\Exceptions
 */
class EventSignaledException extends \Exception
{

    private $event;

    /**
     * EventSignaledException constructor.
     * @param Event|null $event
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Event $event = null, string $message = "Cet évènement a été invalidé", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->event = $event;
    }

    /**
     * Get signaled event
     * @return Event
     */
    public function getEvent(): Event {
        return $this->event;
    }

}