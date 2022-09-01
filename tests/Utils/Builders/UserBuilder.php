<?php

namespace Tests\Utils\Builders;

use Domain\Entities\User;
use Domain\ValueObjects\FrenchDate;
use Domain\ValueObjects\Location;

class UserBuilder
{
    public int          $id              = 0;
    public string       $email           = 'john.doe@dev.fr';
    public string       $firstname       = 'John';
    public string       $lastname        = 'Doe';
    public ?string      $picture         = null;
    public ?string      $description     = null;
    private FrenchDate  $birthDate;
    private Location    $location;
    private string      $validationToken = '1';
    private string      $genre           = 'M';
    private FrenchDate  $createdAt;
    private ?FrenchDate $deletedAt       = null;

    private function __construct()
    {
        $this->birthDate = new FrenchDate(time());
        $this->location = new Location(0, 0);
        $this->createdAt = new FrenchDate(time());
    }

    public static function given(): UserBuilder
    {
        return new self();
    }

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
            location: $this->location,
            validationToken: $this->validationToken,
            genre: $this->genre,
            createdAt: $this->createdAt,
            deletedAt: $this->deletedAt
        );
    }
}