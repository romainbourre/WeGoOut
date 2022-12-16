<?php

namespace Business\Common\Guards;

use Business\Exceptions\ValidationException;

readonly class StringGuard
{

    private function __construct(public string $value)
    {
    }

    public static function from(string $value): self
    {
        return new self($value);
    }

    /**
     * @throws ValidationException
     */
    public function isNotEmpty(string $message): self
    {
        Guard::against(fn() => empty($this->value), $message);
        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function isNotLongerThan(int $maxLength, string $message): self
    {
        Guard::against(fn() => strlen($this->value) > $maxLength, $message);
        return $this;
    }
}
