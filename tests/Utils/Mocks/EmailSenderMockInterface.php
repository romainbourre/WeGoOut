<?php

namespace Tests\Utils\Mocks;

use Business\Entities\User;
use Business\Ports\EmailSenderInterface;

class EmailSenderMockInterface implements EmailSenderInterface
{
    private bool  $validationTokenSent   = false;
    private array $newPasswordOfUserSent = [];

    public function validationTokenSent(): bool
    {
        return $this->validationTokenSent;
    }

    public function newPasswordForUserSent(User $user, string $nextPassword): bool
    {
        return $this->newPasswordOfUserSent[$user->id] == $nextPassword;
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

    public function sendNewPasswordToUser(User $user, string $nextPassword): void
    {
        $this->newPasswordOfUserSent[$user->id] = $nextPassword;
    }

    public function noNewPasswordSent(): bool
    {
        return empty($this->newPasswordOfUserSent);
    }
}