<?php

namespace Tests\Utils\Providers;

use DateTime;
use Domain\Interfaces\DateTimeProviderInterface;

class DeterministDateTimeProvider implements DateTimeProviderInterface
{
    private ?DateTime $dateTime = null;

    public function setNext(DateTime $nextDateTime): void
    {
        $this->dateTime = $nextDateTime;
    }

    public function getNext(): DateTime
    {
        if ($this->dateTime == null) {
            return new DateTime();
        }
        return $this->dateTime;
    }
}