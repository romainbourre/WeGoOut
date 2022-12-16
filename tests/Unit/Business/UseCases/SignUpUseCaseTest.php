<?php

namespace Tests\Unit\Business\UseCases;

use Adapters\InMemory\Repositories\InMemoryUserRepository;
use Business\Entities\User;
use Business\Exceptions\UserAlreadyExistException;
use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;
use Business\UseCases\SignUp\SignUpRequest;
use Business\UseCases\SignUp\SignUpUseCase;
use Business\ValueObjects\FrenchDate;
use Business\ValueObjects\Location;
use DateTime;
use Exception;
use PHPUnit\Framework\TestCase;
use Tests\Utils\Builders\SignUpRequestBuilder;
use Tests\Utils\Builders\UserBuilder;
use Tests\Utils\Encoders\SimplePasswordEncoder;
use Tests\Utils\Mocks\EmailSenderMockInterface;
use Tests\Utils\Providers\DeterministDateTimeProvider;
use Tests\Utils\Providers\DeterministTokenProvider;

class SignUpUseCaseTest extends TestCase
{
    private readonly InMemoryUserRepository $userRepository;
    private readonly DeterministDateTimeProvider $dateTimeProvider;
    private readonly SimplePasswordEncoder $passwordEncoder;
    private readonly DeterministTokenProvider $tokenProvider;
    private readonly EmailSenderMockInterface $emailSenderMock;
    private readonly SignUpUseCase $useCase;

    public function setUp(): void
    {
        $this->userRepository = new InMemoryUserRepository();
        $this->passwordEncoder = new SimplePasswordEncoder();
        $this->dateTimeProvider = new DeterministDateTimeProvider();
        $this->tokenProvider = new DeterministTokenProvider();
        $this->emailSenderMock = new EmailSenderMockInterface();
        $this->useCase = new SignUpUseCase(
            $this->passwordEncoder,
            $this->dateTimeProvider,
            $this->tokenProvider,
            $this->emailSenderMock,
            $this->userRepository
        );
    }

    /**
     * @throws Exception
     */
    public function testThat_Given_GoodData_When_SignUp_Then_SaveUser()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withFirstname('Romain')
            ->withLastname('Bourré')
            ->withEmail('romain.bourre@me.com')
            ->withBirthdate('12/06/1990')
            ->withCity('92140', 'Clamart')
            ->withCoordinates(0, 0)
            ->withPassword('azerty')
            ->create();
        $this->test_Given_Request_When_SignUp_Then_SaveUser(0, $request);

        $request = SignUpRequestBuilder::givenRequest()->create();
        $this->test_Given_Request_When_SignUp_Then_SaveUser(1, $request);
    }

    /**
     * @throws Exception
     */
    private function test_Given_Request_When_SignUp_Then_SaveUser(int $expectedId, SignUpRequest $request)
    {
        $nextProvidedDateTime = new DateTime();
        $this->dateTimeProvider->setNext($nextProvidedDateTime);
        $nextValidationToken = 'this is a validation token';
        $this->tokenProvider->setNext($nextValidationToken);

        $this->useCase->handle($request);

        $savedUser = $this->userRepository->getUserByEmailAndPassword(
            $request->email,
            $this->passwordEncoder->encode($request->password)
        );
        $expectedLocation = new Location($request->postalCode, $request->city, $request->latitude, $request->longitude);
        $this->assertEquals(
            new User(
                id: $expectedId,
                email: $request->email,
                firstname: $request->firstname,
                lastname: $request->lastname,
                picture: null,
                description: null,
                birthDate: FrenchDate::parse($request->birthDate),
                location: $expectedLocation,
                validationToken: $nextValidationToken,
                genre: $request->genre,
                createdAt: new FrenchDate($nextProvidedDateTime->getTimestamp()),
                deletedAt: null
            ),
            $savedUser
        );
    }

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function testThat_Given_GoodDate_When_SignUp_Then_ReturnUser()
    {
        $nextProvidedDateTime = new DateTime();
        $this->dateTimeProvider->setNext($nextProvidedDateTime);
        $nextValidationToken = 'this is a validation token';
        $this->tokenProvider->setNext($nextValidationToken);
        $request = SignUpRequestBuilder::givenRequest()->create();

        $savedUser = $this->useCase->handle($request);

        $expectedLocation = new Location($request->postalCode, $request->city, $request->latitude, $request->longitude);
        $this->assertSame(0, $savedUser->id);
        $this->assertEquals(
            new User(
                id: 0,
                email: $request->email,
                firstname: $request->firstname,
                lastname: $request->lastname,
                picture: null,
                description: null,
                birthDate: FrenchDate::parse($request->birthDate),
                location: $expectedLocation,
                validationToken: $nextValidationToken,
                genre: $request->genre,
                createdAt: new FrenchDate($nextProvidedDateTime->getTimestamp()),
                deletedAt: null
            ),
            $savedUser
        );
    }

    public function testThat_GivenSavedUser_When_SignUpWithSameEmail_Then_PreventError()
    {
        $nextProvidedDateTime = new DateTime();
        $this->dateTimeProvider->setNext($nextProvidedDateTime);
        $nextValidationToken = 'this is a validation token';
        $this->tokenProvider->setNext($nextValidationToken);
        $userEmail = 'charle@dev.com';
        $this->userRepository->addUserWithPassword(UserBuilder::given()->withEmail($userEmail)->create(), 'password');
        $request = SignUpRequestBuilder::givenRequest()->withEmail($userEmail)->create();
        $this->expectException(UserAlreadyExistException::class);
        $this->useCase->handle($request);
    }

    public function testThat_Given_GoodData_When_SignUp_Then_SendValidationToken()
    {
        $request = SignUpRequestBuilder::givenRequest()->create();
        $this->useCase->handle($request);
        $this->assertTrue($this->emailSenderMock->validationTokenSent());
    }

    public function testThat_Given_EmptyFirstname_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withFirstname('')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_FIRSTNAME);
        $this->useCase->handle($request);
    }

    public function testThat_Given_EmptyLastname_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withLastname('')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_LASTNAME);
        $this->useCase->handle($request);
    }

    public function testThat_Given_IncorrectEmail_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withEmail('bademail')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_EMAIL);
        $this->useCase->handle($request);
    }

    public function testThat_Given_PostalCodeWithLetter_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withCity('A2345', 'Paris')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_POSTAL_CODE);
        $this->useCase->handle($request);
    }

    public function testThat_Given_TooShortPostalCode_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withCity('1234', 'Paris')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_POSTAL_CODE);
        $this->useCase->handle($request);
    }

    public function testThat_Given_TooLongPostalCode_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withCity('123456', 'Paris')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_POSTAL_CODE);
        $this->useCase->handle($request);
    }

    public function testThat_Given_EmptyCityName_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withCity('12345', '')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_CITY);
        $this->useCase->handle($request);
    }

    public function testThat_Given_TooShortPassword_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withPassword('abcde')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_PASSWORD);
        $this->useCase->handle($request);
    }

    public function testThat_Given_IncorrectBirthDate_When_SignUp_Then_PreventError()
    {
        $request = SignUpRequestBuilder::givenRequest()
            ->withBirthdate('zqf34')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_BIRTHDATE);
        $this->useCase->handle($request);
    }

    public function testThat_Given_BirthdateThatIsMinor_When_SignUp_Then_PreventError()
    {
        $nextProvidedDateTime = DateTime::createFromFormat('d/m/Y', '11/03/2018');
        $this->dateTimeProvider->setNext($nextProvidedDateTime);
        $request = SignUpRequestBuilder::givenRequest()
            ->withBirthdate('12/03/2000')->create();
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage(ValidationErrorMessages::INCORRECT_BIRTHDATE);
        $this->useCase->handle($request);
    }
}
