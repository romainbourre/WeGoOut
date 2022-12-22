<?php

namespace Business\Ports;

interface InvitationRepositoryInterface
{

    public function isGuestOfEvent(int $userId, string $eventId): bool;
}
