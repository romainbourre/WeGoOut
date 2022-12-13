<?php


namespace Adapters\SendGrid;


use Business\Entities\User;
use Business\Exceptions\TemplateLoadingException;
use Business\Ports\EmailSenderInterface;
use Business\Ports\TemplateRendererInterface;
use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use System\Configuration\ConfigurationInterface;
use System\Logging\LoggerInterface;

readonly class SendGridAdapter implements EmailSenderInterface
{
    private string $apiKey;

    public function __construct(
        private ConfigurationInterface    $configuration,
        private LoggerInterface           $logger,
        private TemplateRendererInterface $templateRenderer
    )
    {
        $this->apiKey = $this->configuration['SendGrid:ApiKey'];
    }

    public function sendHtmlEmail(
        string $toEmail,
        ?string $toName,
        string $fromEmail,
        string $fromName,
        string $subject,
        string $message
    ): bool {
        try {
            $email = new Mail();
            $email->setFrom($fromEmail, $fromName);
            $email->setSubject($subject);
            $email->addTo($toEmail, $toName);
            $email->addContent("text/html", $message);

            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;
        } catch (Exception $e) {
            $this->logger->logError($e->getMessage(), $e);
            return false;
        }
    }

    /**
     * @throws TemplateLoadingException
     */
    public function sendValidationTokenOfUser(User $user): void
    {
        $userEmail = $user->email;
        $userFullName = "$user->firstname $user->lastname";
        $userFirstname = $user->firstname;
        $applicationName = $this->configuration['Application:Name'];
        $companyEmailContact = $this->configuration['Application:Email'];
        $companyWebsite = $this->configuration['Application:Domain'];
        $subjectOfEmail = "Validation de votre compte";
        $emailContent = $this->templateRenderer->render(
            'AccountValidationEmail.html.twig',
            [
                'name' => $userFirstname,
                'applicationName' => $applicationName,
                'website' => $companyWebsite,
                'token' => $user->validationToken
            ]
        );
        $this->sendHtmlEmail(
            $userEmail,
            $userFullName,
            $companyEmailContact,
            $applicationName,
            $subjectOfEmail,
            $emailContent
        );
    }

    /**
     * @throws TemplateLoadingException
     */
    public function sendNewPasswordToUser(User $user, string $nextPassword): void
    {
        $userEmail = $user->email;
        $userFullName = "$user->firstname $user->lastname";
        $applicationName = $this->configuration['Application:Name'];
        $companyEmailContact = $this->configuration['Application:Email'];
        $companyWebsite = $this->configuration['Application:Domain'];
        $subjectOfEmail = 'Votre nouveau mot de passe';
        $emailContent = $this->templateRenderer->render(
            'ResetPasswordEmail.html.twig',
            [
                'website' => $companyWebsite,
                'newPassword' => $nextPassword
            ]
        );
        $this->sendHtmlEmail(
            $userEmail,
            $userFullName,
            $companyEmailContact,
            $applicationName,
            $subjectOfEmail,
            $emailContent
        );
    }
}
