<?php

namespace WebApp\Routing;

use DateTime;

readonly class NonNullParameter
{
    public function __construct(private string $value)
    {
    }

    public function asDatetime(): DateTime
    {
        return DateTime::createFromFormat('d/M/y', $this->value);
    }

    public function asInt(): int
    {
        return (int)$this->value;
    }

    public function asFloat(): float
    {
        return (float)$this->value;
    }

    public function asBool(): bool
    {
        return strtolower($this->value) === 'true';
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    public function asString(): string
    {
        return $this->value;
    }
}
