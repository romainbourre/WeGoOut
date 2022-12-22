<?php

namespace Adapters\DateTimeProvider;

use Business\Ports\DateTimeProviderInterface;
use DateTime;

class DateTimeProvider implements DateTimeProviderInterface
{

    public function current(): DateTime
    {
        return new DateTime();
    }
}
