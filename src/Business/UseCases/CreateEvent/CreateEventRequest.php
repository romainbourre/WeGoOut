<?php


namespace Business\UseCases\CreateEvent;


use DateTime;

readonly class CreateEventRequest
{
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PRIVATE = 'private';

    public function __construct(
        public string    $title,
        public string    $visibility,
        public int       $categoryId,
        public string    $description,
        public bool      $isGuestsOnly,
        public ?int      $participantsLimit,
        public DateTime  $startAt,
        public ?DateTime $endAt,
        public string    $address,
        public string    $addressDetails,
        public string    $postalCode,
        public string    $city,
        public string    $country,
        public float     $latitude,
        public float     $longitude
    )
    {
    }
}
