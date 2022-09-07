<?php

namespace Infrastructure\DateTimeProvider;

use DateTime;
use Domain\Interfaces\DateTimeProviderInterface;

class DateTimeProvider implements DateTimeProviderInterface
{

    public function getNext(): DateTime
    {
        return new DateTime();
    }
}