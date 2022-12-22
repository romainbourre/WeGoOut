<?php

namespace Business\UseCases\SearchEvent\Response;

use DateTime;


readonly class FoundedEvent
{
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    public function __construct(
        public string            $id,
        public FoundedEventOwner $owner,
        public string            $category,
        public string            $visibility,
        public string            $title,
        public DateTime          $startAt,
        public ?DateTime         $endAt,
        public string            $city,
        public float             $distance,
        public ?int              $participantsLimit,
        public bool              $isOwner,
        public bool              $isParticipant,
        public bool              $isAwaitingParticipant,
        public bool              $isGuest,
    )
    {
    }
}
