<?php

namespace Business\UseCases\SearchEvent;

use Business\Entities\EventVisibilities;
use Business\Entities\SavedEvent;
use Business\Exceptions\NonConnectedUserException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\DateTimeProviderInterface;
use Business\Ports\EventRepositoryInterface;
use Business\Ports\InvitationRepositoryInterface;
use Business\Ports\ParticipationRepositoryInterface;
use Business\UseCases\SearchEvent\Response\FoundedEvent;
use Business\UseCases\SearchEvent\Response\FoundedEventOwner;
use Business\UseCases\SearchEvent\Response\SearchEventsWithCriteriaResponse;
use Business\ValueObjects\GeometricCoordinates;

readonly class SearchEventsWithCriteriaUseCase
{
    private const DEFAULT_RESEARCH_KM_DISTANCE = 30;

    public function __construct(
        private AuthenticationContextInterface   $authenticationContext,
        private EventRepositoryInterface         $eventRepository,
        private DateTimeProviderInterface        $dateTimeProvider,
        private ParticipationRepositoryInterface $participationRepository,
        private InvitationRepositoryInterface    $invitationRepository,
    )
    {
    }

    /**
     * @throws NonConnectedUserException
     */
    public function handle(SearchEventsWithCriteriaRequest $request): SearchEventsWithCriteriaResponse
    {
        if (($connectedUser = $this->authenticationContext->getConnectedUser()) == null) {
            throw new NonConnectedUserException();
        }

        if (is_null($request->latitude) || is_null($request->longitude)) {
            $baseLocation = $connectedUser->location;
        } else {
            $baseLocation = new GeometricCoordinates($request->latitude, $request->longitude);
        }

        $events = $this->eventRepository->all();
        $filteredEvents = $events->where(function (SavedEvent $event) use ($connectedUser, $request, $baseLocation) {
            if (!is_null($request->categoryId) && $event->category->id != $request->categoryId) {
                return false;
            }
            $isParticipantOfEvent = $this->participationRepository->isUserParticipantOfEvent($connectedUser->id, $event->id);
            $isGuestOfEvent = $this->invitationRepository->isGuestOfEvent($connectedUser->id, $event->id);
            if ($event->isPrivate() && $event->isNotOwnedBy($connectedUser) && !$isParticipantOfEvent && !$isGuestOfEvent) {
                return false;
            }
            $fromDate = $request->from ?? $this->dateTimeProvider->current();
            if ($event->isFinishedForDate($fromDate)) {
                return false;
            }
            return $event->location->getKilometersDistance($baseLocation) <= ($request->distance ?? self::DEFAULT_RESEARCH_KM_DISTANCE);
        });

        return new SearchEventsWithCriteriaResponse(
            count: $filteredEvents->count(),
            events: $filteredEvents->select(function (SavedEvent $event) use ($connectedUser, $baseLocation) {
                $numberOfParticipantsOfEvent = $this->participationRepository->numberOfParticipantsOfEvent($event->id);
                $isParticipantOfEvent = $this->participationRepository->isUserParticipantOfEvent($connectedUser->id, $event->id);
                $isAwaitingParticipantOfEvent = $this->participationRepository->isUserAwaitingParticipantOfEvent($connectedUser->id, $event->id);
                $isGuestOfEvent = $this->invitationRepository->isGuestOfEvent($connectedUser->id, $event->id);
                return new FoundedEvent(
                    id: $event->id,
                    owner: new FoundedEventOwner(
                        id: $event->owner->id,
                        name: "{$event->owner->firstname} {$event->owner->lastname}",
                    ),
                    category: $event->category->name,
                    visibility: $event->visibility === EventVisibilities::PUBLIC ? FoundedEvent::VISIBILITY_PUBLIC : FoundedEvent::VISIBILITY_PRIVATE,
                    title: $event->title,
                    startAt: $event->dateRange->startAt,
                    endAt: $event->dateRange->endAt,
                    city: $event->location->city,
                    distance: round($event->location->getKilometersDistance($baseLocation), 1),
                    numberOfParticipants: $numberOfParticipantsOfEvent,
                    participantsLimit: $event->participantsLimit,
                    isOwner: $connectedUser->id == $event->owner->id,
                    isParticipant: $isParticipantOfEvent,
                    isAwaitingParticipant: $isAwaitingParticipantOfEvent,
                    isGuest: $isGuestOfEvent
                );
            })->toArray(),
        );
    }
}
