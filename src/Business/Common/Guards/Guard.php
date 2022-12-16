<?php

namespace Business\Common\Guards;

use Business\Exceptions\ValidationException;

class Guard
{
    /**
     * @throws ValidationException
     */
    public static function against(callable $condition, string $message): void
    {
        if ($condition()) {
            throw new ValidationException($message);
        }
    }
}
