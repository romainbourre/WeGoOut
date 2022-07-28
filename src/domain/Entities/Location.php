<?php

namespace Domain\Entities
{

    /**
     * Class Location
     * Represent location
     * @package App\Lib
     * @author
     */
    class Location
    {

        private $latitude;
        private $longitude;
        private $altitude;
        private $label;
        private $complements;
        private $address;
        private $postalCode;
        private $city;
        private $country;
        private $googlePlaceId;

        /**
         * Location constructor.
         * @param float $lat Latitude
         * @param float $lng Longitude
         * @param float $alt Altitude
         */
        public function __construct(float $lat, float $lng, float $alt = 0)
        {
            if (is_float($lat) && is_float($lng))
            {
                $this->latitude = $lat;
                $this->longitude = $lng;
                $this->altitude = $alt;
            }
        }

        /**
         * Get latitude of the location
         * @return float latitude
         */
        public function getLatitude(): float
        {
            return $this->latitude;
        }

        /**
         * Get longitude of the location
         * @return float longitude
         */
        public function getLongitude(): float
        {
            return $this->longitude;
        }

        /**
         * Get altitude of the location
         * @return float altitude
         */
        public function getAltitude(): float
        {
            return $this->altitude;
        }

        /**
         * Get the label of location
         * @return string label
         */
        public function getLabel(): string
        {
            return (string)$this->label;
        }

        /**
         * @return mixed
         */
        public function getComplements()
        {
            return $this->complements;
        }

        /**
         * @return mixed
         */
        public function getAddress(): string
        {
            return $this->address;
        }

        /**
         * @return mixed
         */
        public function getPostalCode(): string
        {
            return $this->postalCode;
        }

        /**
         * Get city of location
         * @return string city
         */
        public function getCity(): string
        {
            return (string)$this->city;
        }

        /**
         * Get country of location
         * @return string country
         */
        public function getCountry(): string
        {
            return (string)$this->country;
        }

        /**
         * Generate smart address
         * @return string
         */
        public function getSmartAddress(): string
        {
            $address = "";
            if (!empty($this->address)) $address .= $this->address . ", ";
            if (!empty($this->postalCode)) $address .= $this->postalCode . " ";
            $address .= $this->city;
            return $address;
        }

        /**
         * Get Google place Id of location
         * @return string
         */
        public function getGooglePlaceId(): string
        {
            return $this->googlePlaceId;
        }

        /**
         * Set label of location
         * @param string $label label
         */
        public function setLabel(string $label): void
        {
            $this->label = $label;
        }

        /**
         * Set Google place Id of location
         * @param mixed $googlePlaceId place Id
         */
        public function setGooglePlaceId(string $googlePlaceId)
        {
            $this->googlePlaceId = $googlePlaceId;
        }

        /**
         * Set address complements of location
         * @param string $complements
         */
        public function setComplements(string $complements)
        {
            $this->complements = $complements;
        }

        /**
         * Set address of location
         * @param string $address address
         */
        public function setAddress(string $address)
        {
            $this->address = $address;
        }

        /**
         * Set postal code of location
         * @param mixed $postalCode postal code
         */
        public function setPostalCode(string $postalCode)
        {
            $this->postalCode = $postalCode;
        }

        /**
         * Set city of location
         * @param null|string $city city
         */
        public function setCity(string $city)
        {
            $this->city = $city;
        }

        /**
         * Set country of location
         * @param string $country country
         */
        public function setCountry(string $country)
        {
            $this->country = $country;
        }

        /**
         * Get distance between this location and other
         * @param Location $location location
         * @return float meters
         */
        public function getDistance(Location $location): float
        {

            //rayon de la terre
            $r = 6371;
            $lat1 = deg2rad($this->latitude);
            $lat2 = deg2rad($location->getLatitude());
            $lon1 = deg2rad($this->longitude);
            $lon2 = deg2rad($location->getLongitude());

            //recuperation altitude en km
            $alt1 = $this->altitude / 1000;
            $alt2 = $location->getAltitude() / 1000;

            //calcul précis
            $dp = 2 * asin(sqrt(pow(sin(($lat1 - $lat2) / 2), 2) + cos($lat1) * cos($lat2) * pow(sin(($lon1 - $lon2) / 2), 2)));

            //sortie en km
            $d = $dp * $r;

            //Pythagore a dit que :
            $h = sqrt(pow($d, 2) + pow($alt2 - $alt1, 2));

            //On remet le résultat en kilomètre
            $d = $h * 1000;

            return $d;

        }
    }
}


