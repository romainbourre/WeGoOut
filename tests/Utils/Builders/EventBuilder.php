<?php

namespace Tests\Utils\Builders;

use Business\Entities\EventCategory;
use Business\Entities\EventOwner;
use Business\Entities\EventVisibilities;
use Business\Entities\NewEvent;
use Business\Entities\SavedEvent;
use Business\Entities\User;
use Business\Exceptions\ValidationException;
use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;
use DateTime;

class EventBuilder
{
    private ?EventOwner $owner;
    private float $latitude = 0;
    private float $longitude = 0;
    private EventVisibilities $visibility = EventVisibilities::PUBLIC;
    private ?DateTime $startAt = null;
    private ?DateTime $endAt = null;
    private ?EventCategory $category = null;

    /**
     * @throws ValidationException
     */
    public function create(): SavedEvent
    {
        $event = new NewEvent(
            visibility: $this->visibility,
            owner: $this->owner ?? $this->createOwner(),
            title: 'my event',
            category: $this->category ?? new EventCategory(0, 'my category'),
            dateRange: new EventDateRange(
                startAt: $this->startAt ?? new DateTime('tomorrow'),
                endAt: $this->endAt,
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
                latitude: $this->latitude,
                longitude: $this->longitude,
            )
        );
        return new SavedEvent('0', $event);
    }

    /**
     * @throws ValidationException
     */
    private function createOwner(): EventOwner
    {
        $user = UserBuilder::given()->create();
        return new EventOwner($user->id, $user->firstname, $user->lastname);
    }

    public function withOwner(User $owner): self
    {
        $this->owner = new EventOwner($owner->id, $owner->firstname, $owner->lastname);
        return $this;
    }

    public function withLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function withLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function asPrivate(): self
    {
        $this->visibility = EventVisibilities::PRIVATE;
        return $this;
    }

    public function thatStartAt(DateTime $startAt): self
    {
        $this->startAt = $startAt;
        return $this;
    }

    public function thatEndAt(?DateTime $endAt): self
    {
        $this->endAt = $endAt;
        return $this;
    }

    public function withCategory(EventCategory $category): self
    {
        $this->category = $category;
        return $this;
    }

    public static function given(): self
    {
        return new self();
    }
}
