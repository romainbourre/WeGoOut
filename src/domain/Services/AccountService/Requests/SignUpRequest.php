<?php


namespace Domain\Services\AccountService\Requests
{


    use DateTime;

    class SignUpRequest
    {

        public function __construct(
            public string $firstname,
            public string $lastname,
            public string $email,
            public DateTime $birthDate,
            public string $label,
            public string $postalCode,
            public string $city,
            public string $country,
            public string $longitude,
            public string $latitude,
            public string $placeId,
            public string $password,
            public string $genre
        ) {
        }
    }
}