<?php


namespace Business\Services\EventService;


use Business\Entities\Event;
use Business\Entities\User;
use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\ResourceNotFound;
use Business\Ports\EventRepositoryInterface;
use Exception;
use WebApp\Authentication\AuthenticationContext;
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
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function changeRegistrationOfUSerToEvent(int $userId, int $eventId): void
    {
        echo 'test';
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

        echo '2';

        $isParticipantWaitingValidation = $event->isParticipantWait($userToChangeRegistration);
        $isAcceptedParticipant = $event->isParticipantValid($userToChangeRegistration);
        echo $isAcceptedParticipant ? 'true' : 'false';
        echo $isParticipantWaitingValidation ? 'true' : 'false';
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

        echo '3';
        if ($event->isInvited($userToChangeRegistration)) {
            echo '4';
            $event->validateGuest($userToChangeRegistration);
            $this->emitter->emit('event.user.subscribe', $event, $userToChangeRegistration);
            return;
        }

        $event->sendRegistrationAsk($userToChangeRegistration);
        $this->emitter->emit('event.user.ask', $event, $userToChangeRegistration);
    }
}
