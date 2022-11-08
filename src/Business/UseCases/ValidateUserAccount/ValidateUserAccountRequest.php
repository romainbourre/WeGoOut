<?php


namespace Business\UseCases\ValidateUserAccount;


class ValidateUserAccountRequest
{

    public function __construct(public readonly string $token)
    {
    }
}
