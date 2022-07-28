<?php


namespace Domain\Services\EventService\Requests;


class SearchEventsRequest
{
    public ?int $kilometersRadius;
    public ?float $latitude;
    public ?float $longitude;
    public ?int $categoryId;
    public ?int $fromDate;

    /**
     * SearchEventsRequest constructor.
     * @param int|null $kilometersRadius
     * @param float|null $latitude
     * @param float|null $longitude
     * @param int|null $categoryId
     * @param int|null $fromDate
     */
    public function __construct(?int $kilometersRadius = null, ?float $latitude = null, ?float $longitude = null, ?int $categoryId = null, ?int $fromDate = null)
    {
        $this->kilometersRadius = $kilometersRadius;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->categoryId = $categoryId;
        $this->fromDate = $fromDate;
    }
}