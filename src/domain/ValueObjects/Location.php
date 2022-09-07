<?php

namespace Domain\ValueObjects
{


    use Domain\Exceptions\ValidationException;

    class Location
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
            public readonly float $latitude,
            public readonly float $longitude,
            public readonly float $altitude = 0
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

        public function getDistance(Location $location): float
        {
            $earthRayon = 6371;
            $originLatitudeRadian = deg2rad($this->latitude);
            $destinationLatitudeRadian = deg2rad($location->latitude);
            $originLongitudeRadian = deg2rad($this->longitude);
            $destinationLongitudeRadian = deg2rad($location->longitude);
            $originKilometersAltitude = $this->altitude / 1000;
            $destinationKilometersAltitude = $location->altitude / 1000;

            //calcul précis
            $dp = 2 * asin(
                    sqrt(
                        pow(sin(($originLatitudeRadian - $destinationLatitudeRadian) / 2), 2) + cos(
                            $originLatitudeRadian
                        ) * cos($destinationLatitudeRadian) * pow(
                            sin(($originLongitudeRadian - $destinationLongitudeRadian) / 2),
                            2
                        )
                    )
                );

            //sortie en km
            $d = $dp * $earthRayon;

            //Pythagore a dit que :
            $h = sqrt(pow($d, 2) + pow($destinationKilometersAltitude - $originKilometersAltitude, 2));

            //On remet le résultat en kilomètre
            return $h * 1000;
        }
    }
}


