<?php


namespace Domain\Services\AccountService\Requests
{


    use DateTime;

    class SignUpRequest
    {
        public string   $firstname;
        public string   $lastname;
        public string   $email;
        public DateTime $birthDate;
        public string   $label;
        public string   $postalCode;
        public string   $city;
        public string   $country;
        public string   $longitude;
        public string   $latitude;
        public string   $placeId;
        public string   $password;
        public string   $genre;

        /**
         * SignUpRequest constructor.
         * @param string $firstname
         * @param string $lastname
         * @param string $email
         * @param DateTime $birthDate
         * @param string $label
         * @param string $postalCode
         * @param string $city
         * @param string $country
         * @param string $longitude
         * @param string $latitude
         * @param string $placeId
         * @param string $password
         * @param string $genre
         */
        public function __construct(string $firstname, string $lastname, string $email, DateTime $birthDate, string $label,
                                    string $postalCode, string $city, string $country, string $longitude, string $latitude,
                                    string $placeId, string $password, string $genre)
        {
            $this->firstname = $firstname;
            $this->lastname = $lastname;
            $this->email = $email;
            $this->birthDate = $birthDate;
            $this->label = $label;
            $this->postalCode = $postalCode;
            $this->city = $city;
            $this->country = $country;
            $this->longitude = $longitude;
            $this->latitude = $latitude;
            $this->placeId = $placeId;
            $this->password = $password;
            $this->genre = $genre;
        }
    }
}