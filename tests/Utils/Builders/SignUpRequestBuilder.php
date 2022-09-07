<?php

namespace Tests\Utils\Builders;

use Domain\UseCases\SignUp\SignUpRequest;

class SignUpRequestBuilder
{
    private string $firstname  = 'John';
    private string $lastname   = 'Doe';
    private string $email      = 'john.doe@provider.com';
    private string $birthDate  = '04/09/1993';
    private string $label      = '';
    private string $postalCode = '75001';
    private string $city       = 'Paris';
    private string $country    = 'France';
    private float  $longitude  = 4;
    private float  $latitude   = 2;
    private string $placeId    = '';
    private string $password   = 'myPassword';
    private string $genre      = 'M';

    private function __construct()
    {
    }

    public static function givenRequest(): SignUpRequestBuilder
    {
        return new self();
    }

    public function withFirstname(string $firstname): SignUpRequestBuilder
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function withLastname(string $lastname): SignUpRequestBuilder
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function withEmail(string $email): SignUpRequestBuilder
    {
        $this->email = $email;
        return $this;
    }

    public function withBirthdate(string $birthdate): SignUpRequestBuilder
    {
        $this->birthDate = $birthdate;
        return $this;
    }

    public function withCity(string $postalCode, string $city): SignUpRequestBuilder
    {
        $this->postalCode = $postalCode;
        $this->city = $city;
        return $this;
    }

    public function withCoordinates(float $longitude, float $latitude): SignUpRequestBuilder
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
        return $this;
    }

    public function withPassword(string $password): SignUpRequestBuilder
    {
        $this->password = $password;
        return $this;
    }

    public function create(): SignUpRequest
    {
        return new SignUpRequest(
            firstname: $this->firstname,
            lastname: $this->lastname,
            email: $this->email,
            birthDate: $this->birthDate,
            label: $this->label,
            postalCode: $this->postalCode,
            city: $this->city,
            country: $this->country,
            longitude: $this->longitude,
            latitude: $this->latitude,
            placeId: $this->placeId,
            password: $this->password,
            genre: $this->genre
        );
    }
}