<?php

namespace Tests\Utils\Builders;

use Business\Entities\User;
use Business\Exceptions\ValidationException;
use Business\ValueObjects\FrenchDate;
use Business\ValueObjects\Location;
use Exception;

class UserBuilder
{
    private static int $ids = 0;

    public int $id = 0;
    public string $email = 'john.doe@dev.fr';
    public string $firstname = 'John';
    public string $lastname = 'Doe';
    public ?string $picture = null;
    public ?string $description = null;
    private FrenchDate $birthDate;
    private ?string $validationToken = '1';
    private string $genre = 'M';
    private FrenchDate $createdAt;
    private ?FrenchDate $deletedAt = null;
    private float $latitude = 0;
    private float $longitude = 0;

    /**
     * @throws ValidationException
     * @throws Exception
     */
    private function __construct()
    {
        $this->birthDate = new FrenchDate(time());
        $this->createdAt = new FrenchDate(time());
    }

    public function withEmail(string $email): UserBuilder
    {
        $this->email = $email;
        return $this;
    }

    public function withValidationToken(?string $validationToken): UserBuilder
    {
        $this->validationToken = $validationToken;
        return $this;
    }

    public function withId(int $userId): self
    {
        $this->id = $userId;
        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function create(): User
    {
        return new User(
            id: $this->id,
            email: $this->email,
            firstname: $this->firstname,
            lastname: $this->lastname,
            picture: $this->picture,
            description: $this->description,
            birthDate: $this->birthDate,
            location: new Location('75001', 'Paris', $this->latitude, $this->longitude),
            validationToken: $this->validationToken,
            genre: $this->genre,
            createdAt: $this->createdAt,
            deletedAt: $this->deletedAt
        );
    }

    public function withLatitude(float $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function withLongitude(float $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }

    public static function given(): UserBuilder
    {
        return new self();
    }


}
