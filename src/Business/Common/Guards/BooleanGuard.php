<?php

namespace Business\Common\Guards;

use Business\Exceptions\ValidationException;

readonly class BooleanGuard
{

    private function __construct(private bool $value)
    {
    }

    public static function from(bool $value): self
    {
        return new self($value);
    }

    /**
     * @throws ValidationException
     */
    public function isTrue(string $message): self
    {
        Guard::against(fn() => $this->value === false, $message);
        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function isFalse(string $message): self
    {
        Guard::against(fn() => $this->value === true, $message);
        return $this;
    }
}
