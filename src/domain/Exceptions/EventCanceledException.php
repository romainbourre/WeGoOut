<?php

namespace Domain\Exceptions;

use Domain\Entities\Event;
use Throwable;

/**
 * Class EventCanceledException
 * When an event have canceled
 * @package App\Exceptions
 */
class EventCanceledException extends \Exception {

    private $event;

    /**
     * EventCanceledException constructor.
     * @param Event|null $event
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(Event $event = null, string $message = "L'évènement a été annulé", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->event = $event;
    }

    /**
     * Canceled event
     * @return Event
     */
    public function getEvent(): Event {
        return $this->event;
    }

}