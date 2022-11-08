<?php


namespace Business\Services\EventService\Requests
{


    use DateTime;

    class CreateEventRequest
    {
        public const TARGET_PUBLIC = 0;
        public const TARGET_PRIVATE = 1;

        public string $title;
        public int $target;
        public int $category;
        public string $description;
        public bool $isGuestOnly;
        public ?int $participantsNumber;
        public DateTime $startedDatetime;
        public ?DateTime $finishedDatetime;
        public string $location;
        public string $locationDetails;
        public string $placeId;
        public string $address;
        public string $postalCode;
        public string $city;
        public string $country;
        public float $latitude;
        public float $longitude;

        /**
         * CreateEventRequest constructor.
         * @param string $title
         * @param int $target
         * @param int $category
         * @param string $description
         * @param bool $isGuestOnly
         * @param ?int $participantsNumber
         * @param DateTime $startedDatetime
         * @param ?DateTime $finishedDatetime
         * @param string $location
         * @param string $locationDetails
         * @param string $placeId
         * @param string $address
         * @param string $postalCode
         * @param string $city
         * @param string $country
         * @param float $latitude
         * @param float $longitude
         */
        public function __construct(string $title, int $target, int $category, string $description, bool $isGuestOnly, ?int $participantsNumber, DateTime $startedDatetime, ?DateTime $finishedDatetime, string $location,
                                    string $locationDetails, string $placeId, string $address, string $postalCode,
                                    string $city,
                                    string $country, float $latitude, float $longitude)
        {
            $this->title = $title;
            $this->target = $target;
            $this->category = $category;
            $this->description = $description;
            $this->isGuestOnly = $isGuestOnly;
            $this->participantsNumber = $participantsNumber;
            $this->startedDatetime = $startedDatetime;
            $this->finishedDatetime = $finishedDatetime;
            $this->location = $location;
            $this->locationDetails = $locationDetails;
            $this->placeId = $placeId;
            $this->address = $address;
            $this->postalCode = $postalCode;
            $this->city = $city;
            $this->country = $country;
            $this->latitude = $latitude;
            $this->longitude = $longitude;
        }
    }
}