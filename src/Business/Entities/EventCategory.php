<?php

namespace Business\Entities;

readonly class EventCategory
{

    public function __construct(public int $id, public string $name)
    {
    }
}
