<?php


namespace Domain\Services\EventService
{


    use App\Librairies\AppSettings;
    use App\Librairies\Emitter;
    use DateTime;
    use Domain\Entities\Event;
    use Domain\Entities\Location;
    use Domain\Entities\User;
    use Domain\Entities\UserCli;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\DataNotSavedException;
    use Domain\Exceptions\NotAuthorizedException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserHadAlreadyEventsException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Interfaces\IEventRepository;
    use Domain\Services\EventService\Requests\CreateEventRequest;
    use Domain\Services\EventService\Requests\SearchEventsRequest;
    use Exception;
    use PhpLinq\Interfaces\ILinq;
    use PhpLinq\PhpLinq;

    class EventService implements IEventService
    {
        private IEventRepository $eventRepository;
        private Emitter $emitter;

        /**
         * EventService constructor.
         * @param IEventRepository $eventRepository
         * @param Emitter $emitter
         */
        public function __construct(IEventRepository $eventRepository, Emitter $emitter)
        {
            $this->eventRepository = $eventRepository;
            $this->emitter = $emitter;
        }

        /**
         * @inheritDoc
         */
        public function getEvent(string $eventId): Event
        {
            $event = $this->eventRepository->getEvent($eventId);

            if (is_null($event))
            {
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
                    ->findUserEventsNumberForDates($userId, $createEventRequest->startedDatetime, $createEventRequest->finishedDatetime) > 0;

            if ($userHadAlreadyEventsForDate)
            {
                throw new UserHadAlreadyEventsException();
            }

            $cleaned_data = array(
                $userId,
                $createEventRequest->target,
                $createEventRequest->title,
                $createEventRequest->category,
                $createEventRequest->description,
                $createEventRequest->isGuestOnly,
                $createEventRequest->participantsNumber,
                $createEventRequest->startedDatetime,
                $createEventRequest->finishedDatetime,
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
            if ($createEventRequest->target != 1 && $createEventRequest->target != 2)
            {
                throw new BadArgumentException("please select good target to create event");
            }

            $eventTitleMaximumCharacter = 65;
            if (strlen($createEventRequest->title) > $eventTitleMaximumCharacter)
            {
                throw new BadArgumentException("event title cannot exceed ${eventTitleMaximumCharacter}");
            }

            $numberOfCategory = count(Event::getAllCategory());
            if ($createEventRequest->category < 0 || $createEventRequest->category > $numberOfCategory)
            {
                throw new BadArgumentException("please select a correct category to create event ($createEventRequest->category)");
            }

            $minimumParticipantsNumber = (new AppSettings())->getParticipantMinNumber();
            $maximumParticipantsNumber = (new AppSettings())->getParticipantMaxNumber();
            if ($createEventRequest->participantsNumber < $minimumParticipantsNumber ||
                $createEventRequest->participantsNumber > $maximumParticipantsNumber)
            {
                throw new BadArgumentException("please choose a correct number of participants for your event (between ${minimumParticipantsNumber} and ${maximumParticipantsNumber} participants)");
            }

            if ($createEventRequest->target == CreateEventRequest::TARGET_PUBLIC && $createEventRequest->isGuestOnly == true)
            {
                throw new BadArgumentException("guest only argument cannot be true when event is public");
            }

            if ($createEventRequest->target == CreateEventRequest::TARGET_PRIVATE && $createEventRequest->isGuestOnly == true)
            {
                if ($createEventRequest->participantsNumber != null)
                {
                    throw new BadArgumentException("number of participant must be null when event is private");
                }
            }

            if ($createEventRequest->startedDatetime <= new DateTime())
            {
                throw new BadArgumentException("event cannot be start before actual date");
            }

            if ($createEventRequest->finishedDatetime != null && $createEventRequest->finishedDatetime <= $createEventRequest->startedDatetime)
            {
                throw new BadArgumentException("end date of event must be after of start date");
            }

            $maximumLocationDetailsCharacters = 100;
            if (strlen($createEventRequest->locationDetails) > $maximumLocationDetailsCharacters)
            {
                throw new BadArgumentException("location details cannot exceed ${maximumLocationDetailsCharacters} characters");
            }
        }

        /**
         * @inheritDoc
         * @param int $userId
         * @param SearchEventsRequest $searchEventsRequest
         * @return array
         * @throws DataNotSavedException
         * @throws UserDeletedException
         * @throws UserNotExistException
         * @throws UserSignaledException
         */
        public function searchEventsForUser(int $userId, SearchEventsRequest $searchEventsRequest): array
        {
            $user = UserCli::loadUserById($userId);
            $kilometersRadius = $searchEventsRequest->kilometersRadius ?? (new AppSettings())->getDefaultDistance();
            $latitude = $searchEventsRequest->latitude ?? $user->getLocation()->getLatitude();
            $longitude = $searchEventsRequest->longitude ?? $user->getLocation()->getLongitude();
;
            $events = $this->eventRepository->searchEventsForUser($userId,
                                                                  $searchEventsRequest->categoryId,
                                                                  $searchEventsRequest->fromDate);

            $location = new Location($latitude, $longitude);
            $events = $events->where(function (Event $event) use ($kilometersRadius, $searchEventsRequest, $location) {

                $eventLocation = $event->getLocation();
                $distance = $location->getDistance($eventLocation);

                return $distance <= ($kilometersRadius * 1000);
            });

            $eventsByDate = [];
            $events->forEach(function(Event $event) use (&$eventsByDate)
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
            $user = User::loadUserById($userId);

            if (is_null($user))
            {
                throw new ResourceNotFound("user with id $userId not found.");
            }

            $event = $this->getEvent($eventId);

            if ($event->isCreator($user) || $event->isOrganizer($user))
            {
                throw new NotAuthorizedException("creator or organizer user with id $userId cannot change him participation.");
            }

            $isParticipantWaitingValidation = $event->isParticipantWait($user);
            $isAcceptedParticipant = $event->isParticipantValid($user);
            if ($isAcceptedParticipant || $isParticipantWaitingValidation)
            {
                if ($isAcceptedParticipant)
                {
                    $this->emitter->emit('event.user.unsubscribe', $event, $user);
                }

                if ($isParticipantWaitingValidation)
                {
                    $this->emitter->emit('event.user.unrequest', $event, $user);
                }

                $event->unsetRegistration($user);
                return;
            }

            if ($event->isInvited($user))
            {
                $event->setParticipantAsValid($user);
                $this->emitter->emit('event.user.subscribe', $event, $user);
                return;
            }

            $event->sendRegistrationAsk($user);
            $this->emitter->emit('event.user.ask', $event, $user);
        }
    }
}