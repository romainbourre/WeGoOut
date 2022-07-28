<?php


namespace Domain\Services\AccountService\Requests
{


    class ValidateAccountRequest
    {
        /**
         * @var string validation token
         */
        public string $token;

        /**
         * ValidateAccountRequest constructor.
         * @param string $token
         */
        public function __construct(string $token)
        {
            $this->token = $token;
        }
    }
}