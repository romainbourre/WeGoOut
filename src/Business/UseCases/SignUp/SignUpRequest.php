<?php


namespace Business\UseCases\SignUp
{


    class SignUpRequest
    {

        public function __construct(
            public string $firstname,
            public string $lastname,
            public string $email,
            public string $birthDate,
            public string $label,
            public string $postalCode,
            public string $city,
            public string $country,
            public float $longitude,
            public float $latitude,
            public string $placeId,
            public string $password,
            public string $genre
        ) {
        }
    }
}