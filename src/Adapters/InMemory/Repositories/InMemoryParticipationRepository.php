<?php

namespace Adapters\InMemory\Repositories;

use Business\Entities\Participation;
use Business\Entities\SavedEvent;
use Business\Entities\User;
use Business\Ports\ParticipationRepositoryInterface;
use DateTime;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;

class InMemoryParticipationRepository implements ParticipationRepositoryInterface
{
    public ILinq $participants;

    public function __construct()
    {
        $this->participants = new PhpLinq();
    }

    public function haveAlreadyParticipantForEvent(User $connectedUser, SavedEvent $privateEvent): void
    {
        $this->participants->add(new Participation(participant: $connectedUser, event: $privateEvent, sentAt: new DateTime('now'), acceptedAt: new DateTime('now')));
    }

    public function isUserParticipantOfEvent(string $userId, string $eventId): bool
    {
        return $this->participants->any(fn(Participation $participation) => "{$participation->participant->id}" === $userId && "{$participation->event->id}" === $eventId && !is_null($participation->acceptedAt));
    }

    public function haveAlreadyAwaitingParticipantForEvent(User $connectedUser, SavedEvent $privateEvent)
    {
        $this->participants->add(new Participation(participant: $connectedUser, event: $privateEvent, sentAt: new DateTime('now'), acceptedAt: null));
    }

    public function isUserAwaitingParticipantOfEvent(string $userId, string $eventId): bool
    {
        return $this->participants->any(fn(Participation $participation) => "{$participation->participant->id}" === $userId && "{$participation->event->id}" === $eventId && is_null($participation->acceptedAt));
    }

    public function numberOfParticipantsOfEvent(string $eventId): int
    {
        return $this->participants->where(fn(Participation $participation) => "{$participation->event->id}" === $eventId && !is_null($participation->acceptedAt))->count();
    }
}
