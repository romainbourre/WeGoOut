<?php


namespace Business\Services\EventService;


use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\DatabaseErrorException;
use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\ResourceNotFound;
use Business\Exceptions\UserNotExistException;
use Business\Exceptions\ValidationException;
use Business\Ports\EventRepositoryInterface;
use Business\Services\EventService\Requests\SearchEventsRequest;
use Business\ValueObjects\Location;
use Exception;
use WebApp\Authentication\AuthenticationContext;
use WebApp\Librairies\AppSettings;
use WebApp\Librairies\Emitter;

readonly class EventService implements IEventService
{


    public function __construct(
        private AuthenticationContext    $authenticationGateway,
        private EventRepositoryInterface $eventRepository,
        private Emitter                  $emitter
    )
    {
    }

    /**
     * @inheritDoc
     */
    public function getEvent(string $eventId): Event
    {
        $event = $this->eventRepository->getEvent($eventId);

        if (is_null($event)) {
            throw new ResourceNotFound("event with id $eventId not found.");
        }

        return $event;
    }

    /**
     * @inheritDoc
     * @param int $userId
     * @param SearchEventsRequest $searchEventsRequest
     * @return array
     * @throws DatabaseErrorException
     * @throws UserNotExistException
     * @throws ValidationException
     */
    public function searchEventsForUser(int $userId, SearchEventsRequest $searchEventsRequest): array
    {
        $user = User::load($userId);
        $kilometersRadius = $searchEventsRequest->kilometersRadius ?? (new AppSettings())->getDefaultDistance();
        $latitude = $searchEventsRequest->latitude ?? $user->getLocation()->latitude;
        $longitude = $searchEventsRequest->longitude ?? $user->getLocation()->longitude;
        $events = $this->eventRepository->searchEventsForUser(
            $userId,
            $searchEventsRequest->categoryId,
            $searchEventsRequest->fromDate
        );

        $location = new Location($user->location->postalCode, $user->location->city, $latitude, $longitude);
        $events = $events->where(function (Event $event) use ($kilometersRadius, $searchEventsRequest, $location) {
            $eventLocation = $event->getLocation();
            $distance = $location->getDistance($eventLocation);
            return $distance <= $kilometersRadius;
        });

        $eventsByDate = [];
        $events->forEach(function (Event $event) use (&$eventsByDate) {
            $eventsByDate[$event->getDatetimeBegin()][] = $event;
        });

        return $eventsByDate;
    }

    /**
     * @inheritDoc
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function changeRegistrationOfUSerToEvent(int $userId, int $eventId): void
    {
        $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
        $userToChangeRegistration = User::load($userId);

        if (is_null($userToChangeRegistration)) {
            throw new ResourceNotFound("user with id $userId not found.");
        }

        $event = $this->getEvent($eventId);

        if ($event->isCreator($userToChangeRegistration) || $event->isOrganizer($userToChangeRegistration)) {
            throw new NotAuthorizedException(
                "creator or organizer user with id $userId cannot change him participation."
            );
        }

        $isParticipantWaitingValidation = $event->isParticipantWait($userToChangeRegistration);
        $isAcceptedParticipant = $event->isParticipantValid($userToChangeRegistration);
        if ($isAcceptedParticipant || $isParticipantWaitingValidation) {
            if ($isAcceptedParticipant) {
                $this->emitter->emit('event.user.unsubscribe', $event, $userToChangeRegistration);
            }

            if ($isParticipantWaitingValidation) {
                $this->emitter->emit('event.user.unrequest', $event, $userToChangeRegistration);
            }

            $event->cancelRegistrationOfUser($connectedUser, $userToChangeRegistration);
            return;
        }

        if ($event->isInvited($userToChangeRegistration)) {
            $event->validateParticipant($connectedUser, $userToChangeRegistration);
            $this->emitter->emit('event.user.subscribe', $event, $userToChangeRegistration);
            return;
        }

        $event->sendRegistrationAsk($userToChangeRegistration);
        $this->emitter->emit('event.user.ask', $event, $userToChangeRegistration);
    }
}
