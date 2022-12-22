<?php

namespace WebApp\Routing;

use DateTime;

readonly class NullableParameter
{

    public function __construct(private ?string $value)
    {
    }

    public function asDatetime(?DateTime $default = null): ?DateTime
    {
        if ($this->isEmpty()) {
            return $default;
        }
        return DateTime::createFromFormat('d/M/y', $this->value);
    }

    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    public function asInt(?int $default = null): ?int
    {
        if ($this->isEmpty()) {
            return $default;
        }
        return (int)$this->value;
    }

    public function asBool(bool $default = false): bool
    {
        if ($this->isEmpty()) {
            return $default;
        }
        return strtolower($this->value) === 'true';
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    public function asString(?string $default = null): ?string
    {
        if ($this->isEmpty()) {
            return $default;
        }
        return $this->value;
    }

    public function asFloat(?float $default = null): ?float
    {
        if ($this->isEmpty()) {
            return $default;
        }
        return (float)$this->value;
    }
}
