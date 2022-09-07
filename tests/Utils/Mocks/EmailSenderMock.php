<?php

namespace Tests\Utils\Mocks;

use Domain\Entities\User;
use Domain\Interfaces\IEmailSender;

class EmailSenderMock implements IEmailSender
{
    private bool $validationTokenSent = false;

    public function validationTokenSent(): bool
    {
        return $this->validationTokenSent;
    }

    public function sendHtmlEmail(
        string $toEmail,
        ?string $toName,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $message
    ): bool {
        // TODO: Implement sendHtmlEmail() method.
    }

    public function sendValidationTokenOfUser(User $user): void
    {
        $this->validationTokenSent = true;
    }
}