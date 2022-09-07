<?php

namespace Domain\Interfaces;

use DateTime;

interface DateTimeProviderInterface
{
    public function getNext(): DateTime;
}