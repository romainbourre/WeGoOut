<?php

namespace Tests\Utils\Builders;

use Business\Entities\EventCategory;
use Business\Entities\EventVisibilities;
use Business\Entities\NewEvent;
use Business\Exceptions\ValidationException;
use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;
use DateTime;

class EventBuilder
{
    /**
     * @throws ValidationException
     */
    public function create(): NewEvent
    {
        return new NewEvent(
            visibility: EventVisibilities::PUBLIC,
            owner: UserBuilder::given()->create(),
            title: 'my event',
            category: new EventCategory(0, 'my category'),
            dateRange: new EventDateRange(
                startAt: new DateTime('tomorrow'),
                endAt: new DateTime('tomorrow + 1 hour'),
            ),
            description: 'a description of event',
            participantsLimit: 10,
            isGuestsOnly: false,
            location: new EventLocation(
                address: '26 rue de la paix',
                postalCode: '75002',
                city: 'Paris',
                country: 'France',
                addressDetails: 'On the corner of the street',
                latitude: 0,
                longitude: 0,
            )
        );
    }


    public static function given(): self
    {
        return new self();
    }
}
