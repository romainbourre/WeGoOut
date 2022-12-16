<?php

namespace Business\Common\Guards;

use Business\Exceptions\ValidationException;

readonly class NumberGuard
{

    private function __construct(private float $value)
    {
    }

    public static function from(float $value): self
    {
        return new self($value);
    }

    /**
     * @throws ValidationException
     */
    public function isNotUpperThan(float $maxLimit, string $message): self
    {
        Guard::against(fn() => $this->value > $maxLimit, $message);
        return $this;
    }

    /**
     * @throws ValidationException
     */
    public function isNotLowerThan(int $minLimit, string $message): self
    {
        Guard::against(fn() => $this->value < $minLimit, $message);
        return $this;
    }
}
