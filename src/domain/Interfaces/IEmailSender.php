<?php


namespace Domain\Interfaces
{


    use Domain\Entities\User;

    interface IEmailSender
    {
        public function sendHtmlEmail(
            string $toEmail,
            ?string $toName,
            string $fromEmail,
            string $fromName,
            string $subject,
            string $message
        ): bool;

        public function sendValidationTokenOfUser(User $user): void;
    }
}