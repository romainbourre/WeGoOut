<?php

namespace Business\Common\Guards;

use Business\Exceptions\ValidationException;
use DateTime;

readonly class DatetimeGuard
{
    private function __construct(private DateTime $value)
    {
    }

    public static function from(DateTime $value): self
    {
        return new self($value);
    }

    /**
     * @throws ValidationException
     */
    public function isNotBefore(DateTime $dateTime, string $message): self
    {
        Guard::against(fn() => $this->value < $dateTime, $message);
        return $this;
    }
}
