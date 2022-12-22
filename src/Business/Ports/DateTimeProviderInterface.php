<?php

namespace Business\Ports;

use DateTime;

interface DateTimeProviderInterface
{

    public function current(): DateTime;
}
