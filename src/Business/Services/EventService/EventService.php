<?php


namespace Business\Services\EventService
{


    use Business\Entities\Event;
    use Business\Entities\User;
    use Business\Exceptions\BadArgumentException;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\NotAuthorizedException;
    use Business\Exceptions\ResourceNotFound;
    use Business\Exceptions\UserHadAlreadyEventsException;
    use Business\Exceptions\UserNotExistException;
    use Business\Exceptions\ValidationException;
    use Business\Ports\AuthenticationContextInterface;
    use Business\Ports\EventRepositoryInterface;
    use Business\Services\EventService\Requests\SearchEventsRequest;
    use Business\UseCases\CreateEvent\CreateEventRequest;
    use Business\ValueObjects\Location;
    use DateTime;
    use Exception;
    use PhpLinq\Interfaces\ILinq;
    use PhpLinq\PhpLinq;
    use WebApp\Librairies\AppSettings;
    use WebApp\Librairies\Emitter;

    class EventService implements IEventService
    {


        public function __construct(
            private readonly AuthenticationContextInterface $authenticationGateway,
            private readonly EventRepositoryInterface $eventRepository,
            private readonly Emitter $emitter
        ) {
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
         */
        public function createEvent(int $userId, CreateEventRequest $createEventRequest): void
        {
            $this->checkCreateEventRequest($createEventRequest);

            $userHadAlreadyEventsForDate = $this->eventRepository
                    ->findUserEventsNumberForDates(
                        $userId,
                        $createEventRequest->startAt,
                        $createEventRequest->endAt
                    ) > 0;

            if ($userHadAlreadyEventsForDate) {
                throw new UserHadAlreadyEventsException();
            }

            $cleaned_data = array(
                $userId,
                $createEventRequest->visibility,
                $createEventRequest->title,
                $createEventRequest->categoryId,
                $createEventRequest->description,
                $createEventRequest->isGuestsOnly,
                $createEventRequest->participantsLimit,
                $createEventRequest->startAt,
                $createEventRequest->endAt,
                $createEventRequest->location,
                $createEventRequest->locationDetails,
                $createEventRequest->placeId,
                $createEventRequest->address,
                $createEventRequest->postalCode,
                $createEventRequest->city,
                $createEventRequest->country,
                $createEventRequest->latitude,
                $createEventRequest->longitude
            );

            $this->eventRepository->saveEvent($cleaned_data);
        }

        /**
         * @param CreateEventRequest $createEventRequest
         * @throws BadArgumentException
         */
        private function checkCreateEventRequest(CreateEventRequest $createEventRequest)
        {
            if ($createEventRequest->visibility != 1 && $createEventRequest->visibility != 2) {
                throw new BadArgumentException("please select good target to create event");
            }

            $eventTitleMaximumCharacter = 65;
            if (strlen($createEventRequest->title) > $eventTitleMaximumCharacter) {
                throw new BadArgumentException("event title cannot exceed ${eventTitleMaximumCharacter}");
            }

            $numberOfCategory = count(Event::getAllCategory());
            if ($createEventRequest->categoryId < 0 || $createEventRequest->categoryId > $numberOfCategory) {
                throw new BadArgumentException(
                    "please select a correct category to create event ($createEventRequest->categoryId)"
                );
            }

            $minimumParticipantsNumber = (new AppSettings())->getParticipantMinNumber();
            $maximumParticipantsNumber = (new AppSettings())->getParticipantMaxNumber();
            if ($createEventRequest->participantsLimit < $minimumParticipantsNumber ||
                $createEventRequest->participantsLimit > $maximumParticipantsNumber) {
                throw new BadArgumentException(
                    "please choose a correct number of participants for your event (between ${minimumParticipantsNumber} and ${maximumParticipantsNumber} participants)"
                );
            }

            if ($createEventRequest->visibility == CreateEventRequest::VISIBILITY_PUBLIC && $createEventRequest->isGuestsOnly == true) {
                throw new BadArgumentException("guest only argument cannot be true when event is public");
            }

            if ($createEventRequest->visibility == CreateEventRequest::VISIBILITY_PRIVATE && $createEventRequest->isGuestsOnly == true) {
                if ($createEventRequest->participantsLimit != null) {
                    throw new BadArgumentException("number of participant must be null when event is private");
                }
            }

            if ($createEventRequest->startAt <= new DateTime()) {
                throw new BadArgumentException("event cannot be start before actual date");
            }

            if ($createEventRequest->endAt != null && $createEventRequest->endAt <= $createEventRequest->startAt) {
                throw new BadArgumentException("end date of event must be after of start date");
            }

            $maximumLocationDetailsCharacters = 100;
            if (strlen($createEventRequest->locationDetails) > $maximumLocationDetailsCharacters) {
                throw new BadArgumentException(
                    "location details cannot exceed ${maximumLocationDetailsCharacters} characters"
                );
            }
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
            $longitude = $searchEventsRequest->longitude ?? $user->getLocation()->longitude;;
            $events = $this->eventRepository->searchEventsForUser(
                $userId,
                $searchEventsRequest->categoryId,
                $searchEventsRequest->fromDate
            );

            $location = new Location($user->location->postalCode, $user->location->city, $latitude, $longitude);
            $events = $events->where(function (Event $event) use ($kilometersRadius, $searchEventsRequest, $location)
            {
                $eventLocation = $event->getLocation();
                $distance = $location->getDistance($eventLocation);
                return $distance <= $kilometersRadius;
            });

            $eventsByDate = [];
            $events->forEach(function (Event $event) use (&$eventsByDate)
            {
                $eventsByDate[$event->getDatetimeBegin()][] = $event;
            });

            return $eventsByDate;
        }

        /**
         * @inheritDoc
         */
        public function getCategories(): ILinq
        {
            return PhpLinq::fromArray(Event::getAllCategory());
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
}
