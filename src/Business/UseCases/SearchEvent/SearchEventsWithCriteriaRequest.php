<?php

namespace Business\UseCases\SearchEvent;

use DateTime;

readonly class SearchEventsWithCriteriaRequest
{

    public function __construct(
        public ?float    $latitude = null,
        public ?float    $longitude = null,
        public ?string   $categoryId = null,
        public ?int      $distance = null,
        public ?DateTime $from = null,
    )
    {
    }
}
