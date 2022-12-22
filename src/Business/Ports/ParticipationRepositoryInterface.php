<?php

namespace Business\Ports;

interface ParticipationRepositoryInterface
{

    public function isUserParticipantOfEvent(string $userId, string $eventId): bool;

    public function isUserAwaitingParticipantOfEvent(string $userId, string $eventId): bool;
}
