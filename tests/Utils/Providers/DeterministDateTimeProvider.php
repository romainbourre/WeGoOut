<?php

namespace Tests\Utils\Providers;

use Business\Ports\DateTimeProviderInterface;
use DateTime;

class DeterministDateTimeProvider implements DateTimeProviderInterface
{
    private ?DateTime $dateTime = null;

    public function setNext(DateTime $nextDateTime): void
    {
        $this->dateTime = $nextDateTime;
    }

    public function current(): DateTime
    {
        if ($this->dateTime == null) {
            return new DateTime();
        }
        return $this->dateTime;
    }
}
