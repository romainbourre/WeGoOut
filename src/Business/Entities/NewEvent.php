<?php

namespace Business\Entities;

use Business\Common\Guards\BooleanGuard;
use Business\Common\Guards\NumberGuard;
use Business\Common\Guards\StringGuard;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;

readonly class NewEvent
{
    private const MAX_PARTICIPANTS_LIMIT = 20;
    private const MIN_PARTICIPANTS_LIMIT = 5;
    private const MAX_TITLE_LENGTH = 65;

    /**
     * @throws ValidationException
     */
    public function __construct(
        public EventVisibilities $visibility,
        public User              $owner,
        public string            $title,
        public EventCategory     $category,
        public EventDateRange    $dateRange,
        public string            $description,
        public ?int              $participantsLimit,
        public bool              $isGuestsOnly,
        public EventLocation     $location
    )
    {
        StringGuard::from($title)->isNotEmpty(ValidationErrorMessages::INCORRECT_EVENT_TITLE);
        StringGuard::from($title)->isNotLongerThan(self::MAX_TITLE_LENGTH, ValidationErrorMessages::INCORRECT_EVENT_TOO_LONG_TITLE);
        NumberGuard::from($participantsLimit)->isNotUpperThan(self::MAX_PARTICIPANTS_LIMIT, ValidationErrorMessages::TOO_MUCH_PARTICIPANTS);
        NumberGuard::from($participantsLimit)->isNotLowerThan(self::MIN_PARTICIPANTS_LIMIT, ValidationErrorMessages::INSUFFICIENT_PARTICIPANTS);
        BooleanGuard::from($visibility == EventVisibilities::PUBLIC && $this->isGuestsOnly)->isFalse(ValidationErrorMessages::PUBLIC_EVENT_WITH_GUESTS_ONLY);
        BooleanGuard::from($visibility == EventVisibilities::PRIVATE && $this->isGuestsOnly && $this->participantsLimit != null)->isFalse(ValidationErrorMessages::PARTICIPANTS_FOR_GUESTS_ONLY_PRIVATE_EVENT);
    }
}
