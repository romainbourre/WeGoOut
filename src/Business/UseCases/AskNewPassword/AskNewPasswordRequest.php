<?php

namespace Business\UseCases\AskNewPassword;

class AskNewPasswordRequest
{
    public function __construct(public readonly string $email)
    {
    }
}