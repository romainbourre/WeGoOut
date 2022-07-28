<?php


namespace Domain\Services\AccountService\Requests
{


    class ResetPasswordRequest
    {
        /**
         * @var string email to reset  password
         */
        public string $email;

        /**
         * ResetPasswordRequest constructor.
         * @param string $email
         */
        public function __construct(string $email)
        {
            $this->email = $email;
        }
    }
}