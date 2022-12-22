<?php

namespace Business\UseCases\CreateEvent;

use Business\Common\Guards\DatetimeGuard;
use Business\Entities\EventOwner;
use Business\Entities\EventVisibilities;
use Business\Entities\NewEvent;
use Business\Entities\User;
use Business\Exceptions\NonConnectedUserException;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\DateTimeProviderInterface;
use Business\Ports\EventCategoryRepositoryInterface;
use Business\Ports\EventRepositoryInterface;
use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;

readonly class CreateEventUseCase
{


    public function __construct(
        private EventRepositoryInterface         $eventRepository,
        private EventCategoryRepositoryInterface $categoryRepository,
        private DateTimeProviderInterface        $dateTimeProvider,
        private AuthenticationContextInterface   $authenticationContext
    )
    {
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function handle(CreateEventRequest $request): void
    {
        $connectedUser = $this->authenticationContext->getConnectedUser();
        if ($connectedUser == null) {
            throw new NonConnectedUserException();
        }
        $event = $this->createEventFromRequestForUser($connectedUser, $request);
        $this->eventRepository->add($event);
    }

    /**
     * @throws ValidationException
     */
    private function createEventFromRequestForUser(User $owner, CreateEventRequest $request): NewEvent
    {
        $visibility = match ($request->visibility) {
            CreateEventRequest::VISIBILITY_PUBLIC => EventVisibilities::PUBLIC,
            CreateEventRequest::VISIBILITY_PRIVATE => EventVisibilities::PRIVATE,
            default => throw new ValidationException(ValidationErrorMessages::INCORRECT_EVENT_VISIBILITY),
        };

        $category = $this->categoryRepository->getById($request->categoryId);
        if ($category == null) {
            throw new ValidationException(ValidationErrorMessages::EVENT_CATEGORY_NOT_FOUND);
        }

        DatetimeGuard::from($request->startAt)->isNotBefore($this->dateTimeProvider->current(), 'event cannot start before now');

        return new NewEvent(
            $visibility,
            new EventOwner($owner->id, $owner->firstname, $owner->lastname),
            $request->title,
            $category,
            new EventDateRange($request->startAt, $request->endAt),
            $request->description,
            $request->participantsLimit,
            $request->isGuestsOnly,
            new EventLocation(
                $request->address,
                $request->postalCode,
                $request->city,
                $request->country,
                $request->addressDetails,
                $request->latitude,
                $request->longitude
            )
        );
    }
}
