<?php

namespace Business\Entities;

readonly class EventOwner
{

    public function __construct(public string $id, public string $firstname, public string $lastname)
    {
    }
}
