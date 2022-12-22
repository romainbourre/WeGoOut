<?php

namespace Business\Entities;

use DateTime;

readonly class Participation
{
    public function __construct(
        public User       $participant,
        public SavedEvent $event,
        public DateTime   $sentAt,
        public ?DateTime  $acceptedAt,
    )
    {
    }
}
