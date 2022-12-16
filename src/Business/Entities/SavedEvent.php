<?php

namespace Business\Entities;

use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;

readonly class SavedEvent extends NewEvent
{
    public function __construct(public string $id, NewEvent $event)
    {
        parent::__construct(
            visibility: $event->visibility,
            owner: $event->owner,
            title: $event->title,
            category: $event->category,
            dateRange: new EventDateRange(
                startAt: $event->dateRange->startAt,
                endAt: $event->dateRange->endAt,
            ),
            description: $event->description,
            participantsLimit: $event->participantsLimit,
            isGuestsOnly: $event->isGuestsOnly,
            location: new EventLocation(
                address: $event->location->address,
                postalCode: $event->location->postalCode,
                city: $event->location->city,
                country: $event->location->country,
                addressDetails: $event->location->addressDetails,
                latitude: $event->location->latitude,
                longitude: $event->location->longitude,
            )
        );
    }
}
