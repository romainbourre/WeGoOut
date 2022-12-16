<?php

namespace Tests\Utils\Builders;

use Business\UseCases\CreateEvent\CreateEventRequest;
use DateInterval;
use DateTime;

class CreateEventRequestBuilder
{
    private string $visibility = CreateEventRequest::VISIBILITY_PUBLIC;
    private string $title = 'my super event';
    private int $categoryId = 1;
    private int $participantsNumber = 10;
    private bool $isGuestsOnly = false;
    private ?DateTime $startAt = null;
    private ?DateTime $endAt = null;
    private string $locationDetails = 'At the right side of the street';

    public static function given(): self
    {
        return new self();
    }

    public function create(): CreateEventRequest
    {
        return new CreateEventRequest(
            $this->title,
            $this->visibility,
            $this->categoryId,
            'this is my super event',
            $this->isGuestsOnly,
            $this->participantsNumber,
            $this->startAt ?? (new DateTime())->add(new DateInterval('PT1H')),
            $this->endAt,
            'location',
            $this->locationDetails,
            'placeId',
            'address',
            '92140',
            'Clamart',
            'France',
            1.0,
            1.0
        );
    }

    public function asPrivate(): self
    {
        $this->visibility = CreateEventRequest::VISIBILITY_PRIVATE;
        return $this;
    }

    public function withVisibility(string $visibility): self
    {
        $this->visibility = $visibility;
        return $this;
    }

    public function withTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function withCategoryId(int $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    public function withParticipants(int $participants): self
    {
        $this->participantsNumber = $participants;
        return $this;
    }

    public function withGuestsOnly(): self
    {
        $this->isGuestsOnly = true;
        return $this;
    }

    public function thanStartAt(DateTime $date): self
    {
        $this->startAt = $date;
        return $this;
    }

    public function thanEndAt(DateTime $param): self
    {
        $this->endAt = $param;
        return $this;
    }

    public function withLocationDetails(string $locationDetails): self
    {
        $this->locationDetails = $locationDetails;
        return $this;
    }
}
