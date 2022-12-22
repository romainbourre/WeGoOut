<?php

namespace Business\UseCases\SearchEvent\Response;

readonly class SearchEventsWithCriteriaResponse
{

    /**
     * @param int $count
     * @param array<FoundedEvent> $events
     */
    public function __construct(public int $count, public array $events)
    {
    }
}
