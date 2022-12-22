<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryEventCategoryRepository;
use Adapters\InMemory\Repositories\InMemoryEventRepository;
use Business\Entities\EventCategory;
use Business\Entities\EventOwner;
use Business\Entities\EventVisibilities;
use Business\Entities\NewEvent;
use Business\Entities\SavedEvent;
use Business\Entities\User;
use Business\Exceptions\NonConnectedUserException;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\UseCases\CreateEvent\CreateEventRequest;
use Business\UseCases\CreateEvent\CreateEventUseCase;
use Business\ValueObjects\EventDateRange;
use Business\ValueObjects\EventLocation;
use DateTime;
use PhpLinq\Exceptions\InvalidQueryResultException;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\CreateEventRequestBuilder;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Contexts\DeterministAuthenticationContext;
use Tests\Utils\Providers\DeterministDateTimeProvider;

class CreateEventUseCaseTest extends TestCase
{
    private readonly InMemoryEventRepository $eventRepository;
    private readonly CreateEventUseCase $useCase;
    private readonly InMemoryEventCategoryRepository $categoryRepository;
    private readonly DeterministDateTimeProvider $dateTimeProvider;
    private readonly DeterministAuthenticationContext $authenticationContext;

    public function setUp(): void
    {
        $this->eventRepository = new InMemoryEventRepository();
        $this->categoryRepository = new InMemoryEventCategoryRepository();
        $this->dateTimeProvider = new DeterministDateTimeProvider();
        $this->authenticationContext = new DeterministAuthenticationContext();
        $this->useCase = new CreateEventUseCase($this->eventRepository, $this->categoryRepository, $this->dateTimeProvider, $this->authenticationContext);
    }

    /**
     * @throws InvalidQueryResultException|ValidationException|NonConnectedUserException
     */
    public function testThatSavePublicEvent()
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->create();

        $this->useCase->handle($request);

        $savedEvent = $this->eventRepository->events->first();
        $this->assertThatSavedEventCorrespondToRequest($request, $connectedUser, $category, $savedEvent);
    }

    /**
     * @throws ValidationException
     */
    private function assertThatSavedEventCorrespondToRequest(CreateEventRequest $request, User $expectedUserAsOwner, EventCategory $expectedCategory, SavedEvent $savedEvent): void
    {
        $visibility = match ($request->visibility) {
            CreateEventRequest::VISIBILITY_PUBLIC => EventVisibilities::PUBLIC,
            CreateEventRequest::VISIBILITY_PRIVATE => EventVisibilities::PRIVATE,
        };
        $expectedOwner = new EventOwner($expectedUserAsOwner->id, $expectedUserAsOwner->firstname, $expectedUserAsOwner->lastname);
        $expectedEvent = new NewEvent(
            visibility: $visibility,
            owner: $expectedOwner,
            title: $request->title,
            category: $expectedCategory,
            dateRange: new EventDateRange(
                startAt: $request->startAt,
                endAt: $request->endAt,
            ),
            description: $request->description,
            participantsLimit: $request->participantsLimit,
            isGuestsOnly: $request->isGuestsOnly,
            location: new EventLocation(
                address: $request->address,
                postalCode: $request->postalCode,
                city: $request->city,
                country: $request->country,
                addressDetails: $request->addressDetails,
                latitude: $request->latitude,
                longitude: $request->longitude,
            )
        );
        $this->assertEquals(new SavedEvent('0', $expectedEvent), $savedEvent);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException|InvalidQueryResultException
     */
    public function testThatGivenSavedEventWhenSaveNewEventThenShouldNotBeSameId(): void
    {
        $this->eventRepository->haveAlreadyEvent();
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->create();

        $this->useCase->handle($request);

        $lastSavedEvent = $this->eventRepository->events->last();
        $this->assertEquals('1', $lastSavedEvent->id);
    }

    /**
     * @throws InvalidQueryResultException|ValidationException|NonConnectedUserException
     */
    public function testThatSavePrivateEvent(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->asPrivate()
            ->create();

        $this->useCase->handle($request);

        $savedEvent = $this->eventRepository->events->first();
        $this->assertThatSavedEventCorrespondToRequest($request, $connectedUser, $category, $savedEvent);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithFalseVisibilityThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withVisibility("unknown")
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EVENT_VISIBILITY);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithEmptyTitleThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withTitle('')
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EVENT_TITLE);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithToLongTitleThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withTitle('this is a title that is too long to be saved in database. It is sad.')
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EVENT_TOO_LONG_TITLE);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventIfCategoryDoesntExistThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->categoryRepository->haveNoCategories();
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId(30)
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::EVENT_CATEGORY_NOT_FOUND);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithTooMuchParticipantsThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withParticipants(21)
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::TOO_MUCH_PARTICIPANTS);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithInsufficientParticipantsThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withParticipants(4)
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INSUFFICIENT_PARTICIPANTS);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreatePublicEventWithActivatedGuestsOnlyThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withGuestsOnly()
            ->withParticipants(null)
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::PUBLIC_EVENT_WITH_GUESTS_ONLY);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreatePrivateEventWithActivateGuestsOnlyAndParticipantsPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->asPrivate()
            ->withGuestsOnly()
            ->withParticipants(6)
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::PARTICIPANTS_FOR_GUESTS_ONLY_PRIVATE_EVENT);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithStartAtBeforeNowThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $this->dateTimeProvider->setNext(new DateTime());
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->thanStartAt(new DateTime('yesterday'))
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('event cannot start before now');

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithEndDateThanBeforeStartDateThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $this->dateTimeProvider->setNext(new DateTime());
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->thanStartAt(new DateTime('tomorrow'))
            ->thanEndAt(new DateTime('yesterday'))
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::EVENT_CANNOT_END_BEFORE_START);

        $this->useCase->handle($request);
    }

    /**
     * @throws NonConnectedUserException
     */
    public function testThatWhenCreateEventWithTooLongLocationDetailsThenPreventError(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->withLocationDetails('this is a location details that is too long to be saved in database. It is sad. But make sens that rules avoid user to do this.')
            ->create();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('too long location details');

        $this->useCase->handle($request);
    }

    /**
     * @throws ValidationException
     */
    public function testThatGivenUserIsNotConnectedWhenCreateEventThenPreventError(): void
    {
        $category = $this->categoryRepository->haveOneCategory('Manger un morceau');
        $request = CreateEventRequestBuilder::given()
            ->withCategoryId($category->id)
            ->create();

        $this->expectException(NonConnectedUserException::class);
        $this->expectExceptionMessage('non connected user');

        $this->useCase->handle($request);
    }
}
