<?php


namespace Infrastructure\SendGrid;


use Domain\Entities\User;
use Domain\Exceptions\TemplateLoadingException;
use Domain\Interfaces\IEmailSender;
use Domain\Interfaces\ITemplateRenderer;
use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use System\Configuration\IConfiguration;
use System\Logging\ILogger;

class SendGridAdapter implements IEmailSender
{
    private readonly string $apiKey;

    public function __construct(
        private readonly IConfiguration $configuration,
        private readonly ILogger $logger,
        private readonly ITemplateRenderer $templateRenderer
    ) {
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
}