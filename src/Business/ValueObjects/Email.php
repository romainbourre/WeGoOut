<?php

namespace Business\ValueObjects;

use Business\Exceptions\ValidationErrorMessages;
use Business\Exceptions\ValidationException;

class Email
{
    /**
     * @throws ValidationException
     */
    public function __construct(private readonly string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(ValidationErrorMessages::INCORRECT_EMAIL);
        }
    }

    /**
     * @throws ValidationException
     */
    public static function from(string $email): Email
    {
        return new Email($email);
    }

    public function __toString(): string
    {
        return $this->email;
    }
}