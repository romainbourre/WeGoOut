<?php

namespace Adapters\DateTimeProvider;

use Business\Ports\DateTimeProviderInterface;
use DateTime;

class DateTimeProvider implements DateTimeProviderInterface
{

    public function getNext(): DateTime
    {
        return new DateTime();
    }
}