<?php

namespace Adapters\InMemory\Repositories;

use Business\Entities\Invitation;
use Business\Entities\SavedEvent;
use Business\Entities\User;
use Business\Ports\InvitationRepositoryInterface;
use DateTime;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;

readonly class InMemoryInvitationRepository implements InvitationRepositoryInterface
{
    public ILinq $guests;

    public function __construct()
    {
        $this->guests = new PhpLinq();
    }

    public function haveAlreadyGuestForEvent(User $connectedUser, SavedEvent $privateEvent): void
    {
        $this->guests->add(new Invitation(user: $connectedUser, event: $privateEvent, sentAt: new DateTime('now')));
    }

    public function isGuestOfEvent(?int $userUserId, string $eventId): bool
    {
        return $this->guests->any(fn(Invitation $invitation) => $invitation->user->id === $userUserId && $invitation->event->id === $eventId);
    }
}
