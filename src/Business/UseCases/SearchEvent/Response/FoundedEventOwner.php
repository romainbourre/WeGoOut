<?php

namespace Business\UseCases\SearchEvent\Response;

readonly class FoundedEventOwner
{

    public function __construct(public string $id, public string $name)
    {
    }
}
