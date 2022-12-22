<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryEventRepository;
use Adapters\InMemory\Repositories\InMemoryInvitationRepository;
use Adapters\InMemory\Repositories\InMemoryParticipationRepository;
use Adapters\InMemory\Repositories\InMemoryUserRepository;
use Business\Entities\EventCategory;
use Business\Entities\EventVisibilities;
use Business\Entities\SavedEvent;
use Business\Exceptions\NonConnectedUserException;
use Business\Exceptions\ValidationException;
use Business\UseCases\SearchEvent\Response\FoundedEvent;
use Business\UseCases\SearchEvent\Response\FoundedEventOwner;
use Business\UseCases\SearchEvent\Response\SearchEventsWithCriteriaResponse;
use Business\UseCases\SearchEvent\SearchEventsWithCriteriaRequest;
use Business\UseCases\SearchEvent\SearchEventsWithCriteriaUseCase;
use DateTime;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\EventBuilder;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Contexts\DeterministAuthenticationContext;
use Tests\Utils\Providers\DeterministDateTimeProvider;

class SearchEventWithCriteriaUseCaseTest extends TestCase
{
    private readonly DeterministAuthenticationContext $authenticationContext;
    private readonly InMemoryUserRepository $userRepository;
    private readonly InMemoryEventRepository $eventRepository;
    private readonly DeterministDateTimeProvider $dateTimeProvider;
    private readonly InMemoryParticipationRepository $participationRepository;
    private readonly InMemoryInvitationRepository $invitationRepository;
    private readonly SearchEventsWithCriteriaUseCase $useCase;

    public function setUp(): void
    {
        $this->authenticationContext = new DeterministAuthenticationContext();
        $this->userRepository = new InMemoryUserRepository();
        $this->eventRepository = new InMemoryEventRepository();
        $this->dateTimeProvider = new DeterministDateTimeProvider();
        $this->participationRepository = new InMemoryParticipationRepository();
        $this->invitationRepository = new InMemoryInvitationRepository();
        $this->useCase = new SearchEventsWithCriteriaUseCase(
            $this->authenticationContext,
            $this->eventRepository,
            $this->dateTimeProvider,
            $this->participationRepository,
            $this->invitationRepository,
        );
    }

    public function testThatGivenNonConnectedUserWhenSearchEventThenPreventError(): void
    {
        $request = new SearchEventsWithCriteriaRequest(latitude: 0, longitude: 0);

        $this->expectException(NonConnectedUserException::class);
        $this->useCase->handle($request);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function testThatGivenSavedEventWhenSearchEventsWithCoordinatesThenReturnEventsLessThan30km(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->eventRepository->haveAlreadyEvent();
        $this->userRepository->setNextId($connectedUser->id + 1);
        $nearExpectedEvent = $this->eventRepository->haveAlreadyEvent(function (EventBuilder $event) {
            $event
                ->withOwner($this->userRepository->haveAlreadyUser())
                ->withLatitude(48.815441)
                ->withLongitude(2.317755)
                ->create();
        });

        $request = new SearchEventsWithCriteriaRequest(latitude: 48.811898, longitude: 2.271534);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $nearExpectedEvent, 3.4);
    }

    private function assertThatOneEventIsFound(SearchEventsWithCriteriaResponse $result, SavedEvent $savedEvent, float $distance, bool $isOwner = false, bool $isParticipant = false, bool $isAwaitingParticipant = false, bool $isGuest = false): void
    {
        $visibility = $savedEvent->visibility === EventVisibilities::PUBLIC ? FoundedEvent::VISIBILITY_PUBLIC : FoundedEvent::VISIBILITY_PRIVATE;
        $this->assertEquals(new SearchEventsWithCriteriaResponse(
            count: 1,
            events: [
                new FoundedEvent(
                    id: $savedEvent->id,
                    owner: new FoundedEventOwner(
                        id: $savedEvent->owner->id,
                        name: "{$savedEvent->owner->firstname} {$savedEvent->owner->lastname}",
                    ),
                    category: $savedEvent->category->name,
                    visibility: $visibility,
                    title: $savedEvent->title,
                    startAt: $savedEvent->dateRange->startAt,
                    endAt: $savedEvent->dateRange->endAt,
                    city: $savedEvent->location->city,
                    distance: $distance,
                    participantsLimit: $savedEvent->participantsLimit,
                    isOwner: $isOwner,
                    isParticipant: $isParticipant,
                    isAwaitingParticipant: $isAwaitingParticipant,
                    isGuest: $isGuest,
                )
            ]
        ), $result);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function testThatGivenSavedEventWhenSearchEventsWithoutCoordinatesThenReturnEventsLessThan30kmAroundConnectedUser(): void
    {
        $connectedUser = UserBuilder::given()->withLatitude(48.815441)->withLongitude(2.317755)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->eventRepository->haveAlreadyEvent();
        $this->userRepository->setNextId($connectedUser->id + 1);
        $nearExpectedEvent = $this->eventRepository->haveAlreadyEvent(function (EventBuilder $event) use ($connectedUser) {
            $event
                ->withOwner($this->userRepository->haveAlreadyUser())
                ->withLatitude($connectedUser->location->latitude)
                ->withLongitude($connectedUser->location->longitude)
                ->create();
        });

        $request = new SearchEventsWithCriteriaRequest();
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $nearExpectedEvent, 0);
    }

    /**
     * @throws NonConnectedUserException
     * @throws ValidationException
     */
    public function testThatGivenSavedEventWhenSearchEventsWithCoordinatesThenNotReturnEventsMoreThan30km(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->eventRepository->haveAlreadyEvent(function (EventBuilder $event) {
            $event
                ->withOwner($this->userRepository->haveAlreadyUser())
                ->withLatitude(48.5032)
                ->withLongitude(2.4154)
                ->create();
        });

        $request = new SearchEventsWithCriteriaRequest(latitude: 48.811898, longitude: 2.271534);
        $result = $this->useCase->handle($request);

        $this->assertThatNoEventsFound($result);
    }

    private function assertThatNoEventsFound(SearchEventsWithCriteriaResponse $result): void
    {
        $this->assertEquals(new SearchEventsWithCriteriaResponse(
            count: 0,
            events: []
        ), $result);
    }

    /**
     * @throws NonConnectedUserException
     * @throws ValidationException
     */
    public function testThatGivenNearSavedEventOfConnectedUserWhenSearchEventsThenSpecifieThatOwner(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $nearExpectedEvent = $this->eventRepository->haveAlreadyEvent(function (EventBuilder $event) use ($connectedUser) {
            $event
                ->withOwner($connectedUser)
                ->withLatitude(48.815441)
                ->withLongitude(2.317755)
                ->create();
        });

        $request = new SearchEventsWithCriteriaRequest(latitude: 48.811898, longitude: 2.271534);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $nearExpectedEvent, 3.4, true);
    }

    /**
     * @throws ValidationException|NonConnectedUserException
     */
    public function testThatGivenSavedNearPrivateEventWhenSearchNearEventThenNoReturnPrivateEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(2565)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->withLatitude(48.811898)->withLongitude(2.271534)
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatNoEventsFound($result);
    }

    /**
     * @throws NonConnectedUserException
     * @throws ValidationException
     */
    public function testThatGivenSavedEventWithPassedEndDateWhenSearchEventsThenReturnEmptyList(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->dateTimeProvider->setNext(new DateTime('now'));
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->thatStartAt(new DateTime('2 days ago'))
                    ->thatEndAt(new DateTime('yesterday'))
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatNoEventsFound($result);
    }

    /**
     * @throws NonConnectedUserException
     * @throws ValidationException
     */
    public function testThatGivenSavedEventWithNullEndDateAndPassedStartDateWhenSearchEventsThenReturnEmptyList(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->dateTimeProvider->setNext(new DateTime('now'));
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->thatStartAt(new DateTime('1 hour ago'))
                    ->thatEndAt(null)
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatNoEventsFound($result);
    }

    /**
     * @throws ValidationException|NonConnectedUserException
     */
    public function testThatGivenSavedNearPrivateOfConnectedUserEventWhenSearchNearEventThenNoReturnPrivateEvent(): void
    {
        $connectedUser = UserBuilder::given()->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->withOwner($connectedUser)
                    ->withLatitude(48.811898)->withLongitude(2.271534)
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $privateEvent, 0, true);
    }

    /**
     * @throws ValidationException|NonConnectedUserException
     */
    public function testThatGivenConnectedUserIsParticipantForPrivateEventWhenSearchEventsThenReturnPrivateEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->create();
            });
        $this->participationRepository->haveAlreadyParticipantForEvent($connectedUser, $privateEvent);

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $privateEvent, 0, false, true);
    }

    /**
     * @throws ValidationException|NonConnectedUserException
     */
    public function testThatGivenConnectedUserIsAwaitParticipantForPublicEventWhenSearchEventsThenReturnEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $privateEvent = $this->eventRepository->haveAlreadyEvent();
        $this->participationRepository->haveAlreadyAwaitingParticipantForEvent($connectedUser, $privateEvent);

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $privateEvent, 0, false, false, true);
    }

    /**
     * @throws ValidationException|NonConnectedUserException
     */
    public function testThatGivenConnectedUserIsGuestForPrivateEventWhenSearchEventsThenReturnPrivateEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $privateEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->asPrivate()
                    ->create();
            });
        $this->invitationRepository->haveAlreadyGuestForEvent($connectedUser, $privateEvent);

        $request = new SearchEventsWithCriteriaRequest(latitude: $privateEvent->location->latitude, longitude: $privateEvent->location->longitude);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $privateEvent, 0, false, false, false, true);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function testThatGivenSavedEventsWithDifferentCategoryWhenSearchEventsForCategoryThenReturnConcernedEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);
        $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->withCategory(new EventCategory(0, 'drink'))
                    ->create();
            });
        $expectedCategory = new EventCategory(1, 'sport');
        $expectedEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser, $expectedCategory) {
                $event
                    ->withCategory($expectedCategory)
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: $expectedEvent->location->latitude, longitude: $expectedEvent->location->longitude, categoryId: $expectedCategory->id);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $expectedEvent, 0);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function testThatGivenSavedEventMoreThan50kmWhenSearchEventsFor100KmDistanceThenReturnConcernedEvent(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);

        $expectedEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->withLatitude(0.6)->withLongitude(0.6)
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: 0, longitude: 0, distance: 100);
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $expectedEvent, 94.4);
    }

    /**
     * @throws ValidationException
     * @throws NonConnectedUserException
     */
    public function testThatGivenSavedEventsWhenSearchEventsFromDateThenReturnEventAfterThisDate(): void
    {
        $connectedUser = UserBuilder::given()->withId(234)->create();
        $this->authenticationContext->setConnectedUser($connectedUser);

        $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->thatStartAt(new DateTime('tomorrow'))
                    ->create();
            });

        $expectedEvent = $this->eventRepository->haveAlreadyEvent(
            function (EventBuilder $event) use ($connectedUser) {
                $event
                    ->thatStartAt(new DateTime('+3 days'))
                    ->create();
            });

        $request = new SearchEventsWithCriteriaRequest(latitude: 0, longitude: 0, distance: 100, from: new DateTime('+2 days'));
        $result = $this->useCase->handle($request);

        $this->assertThatOneEventIsFound($result, $expectedEvent, 0);
    }
}
