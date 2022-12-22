<?php

namespace Business\Entities;

use DateTime;

class Invitation
{
    public function __construct(
        public User       $user,
        public SavedEvent $event,
        public DateTime   $sentAt,
    )
    {
    }
}
