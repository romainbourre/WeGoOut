<?php

namespace Business\ValueObjects
{


    use Business\Exceptions\ValidationException;

    class Location extends GeometricCoordinates
    {

        private string $label         = '';
        private string $complements   = '';
        private string $address       = '';
        private string $country       = '';
        private string $googlePlaceId = '';


        /**
         * @throws ValidationException
         */
        public function __construct(
            public readonly string $postalCode,
            public readonly string $city,
            float $latitude,
            float $longitude,
            float $altitude = 0
        ) {
            if ($city == '') {
                throw new ValidationException('incorrect city given');
            }
            if (strlen($postalCode) != 5) {
                throw new ValidationException('incorrect postal code given');
            }
            foreach (str_split($postalCode) as $number) {
                if (!ctype_digit($number)) {
                    throw new ValidationException('incorrect postal code given');
                }
            }
            parent::__construct($latitude, $longitude, $altitude);
        }

        public function getLabel(): string
        {
            return $this->label;
        }

        public function getComplements(): string
        {
            return $this->complements;
        }

        public function getAddress(): string
        {
            return $this->address;
        }

        public function getPostalCode(): string
        {
            return $this->postalCode;
        }

        public function getCity(): string
        {
            return (string)$this->city;
        }

        public function getCountry(): string
        {
            return (string)$this->country;
        }

        public function getSmartAddress(): string
        {
            $address = "";
            if (!empty($this->address)) {
                $address .= $this->address . ", ";
            }
            if (!empty($this->postalCode)) {
                $address .= $this->postalCode . " ";
            }
            $address .= $this->city;
            return $address;
        }

        public function getGooglePlaceId(): string
        {
            return $this->googlePlaceId;
        }

        public function setLabel(string $label): void
        {
            $this->label = $label;
        }

        public function setGooglePlaceId(string $googlePlaceId): void
        {
            $this->googlePlaceId = $googlePlaceId;
        }

        public function setComplements(string $complements): void
        {
            $this->complements = $complements;
        }

        public function setAddress(string $address): void
        {
            $this->address = $address;
        }

        public function setCountry(string $country): void
        {
            $this->country = $country;
        }
    }
}


