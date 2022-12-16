<?php

namespace Business\ValueObjects;

use Business\Common\Guards\DatetimeGuard;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use DateTime;

readonly class EventDateRange
{

    /**
     * @throws ValidationException
     */
    public function __construct(public DateTime $startAt, public ?DateTime $endAt)
    {
        if ($this->endAt != null) {
            DatetimeGuard::from($this->endAt)->isNotBefore($this->startAt, ValidationErrorMessages::EVENT_CANNOT_END_BEFORE_START);
        }
    }
}
