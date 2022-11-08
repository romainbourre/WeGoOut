<?php


namespace Business\Ports;


use Business\Entities\User;

interface EmailSenderInterface
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

    public function sendNewPasswordToUser(User $user, string $nextPassword): void;
}
