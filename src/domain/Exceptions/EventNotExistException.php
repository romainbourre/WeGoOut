<?php

namespace Domain\Exceptions;

use Throwable;

/**
 * Class EventNotExistException
 * When asked event no exist
 * @package App\Exceptions
 */
class EventNotExistException extends \Exception {

    /**
     * EventNotExistException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "L'évènement n'existe pas", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}