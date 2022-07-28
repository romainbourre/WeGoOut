<?php


namespace Domain\Interfaces
{


    interface IEmailSender
    {
        public function sendHtmlEmail(string $toEmail, ?string $toName, string $fromEmail, string $fromName, string $subject, string $message): bool;
    }
}