<?php


namespace Infrastructure\SendGrid;


use Domain\Interfaces\IEmailSender;
use Exception;
use SendGrid;
use SendGrid\Mail\Mail;
use System\Logging\ILogger;

class SendGridAdapter implements IEmailSender
{


    public function __construct(private readonly string $apiKey, private readonly ILogger $logger)
    {
    }

    public function sendHtmlEmail(string $toEmail, ?string $toName, string $fromEmail, string $fromName, string $subject, string $message): bool
    {
        try
        {
            $email = new Mail();
            $email->setFrom($fromEmail, $fromName);
            $email->setSubject($subject);
            $email->addTo($toEmail, $toName);
            $email->addContent("text/html", $message);

            $sendgrid = new SendGrid($this->apiKey);
            $response = $sendgrid->send($email);

            return $response->statusCode() >= 200 && $response->statusCode() < 300;

        }
        catch (Exception $e)
        {
            $this->logger->logError($e->getMessage(), $e);
            return false;
        }
    }
}