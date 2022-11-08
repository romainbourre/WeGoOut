<?php


namespace Business\Services\AccountService
{


    use Adapters\PasswordGenerator\PasswordGenerator;
    use Business\Entities\User;
    use Business\Ports\EmailSenderInterface;
    use Business\Ports\TemplateRendererInterface;
    use Business\Ports\UserRepositoryInterface;
    use Infrastructure\PasswordGenerator\PasswordGeneratorInterface;

    class AccountService implements IAccountService
    {
        /**
         * @var UserRepositoryInterface user repository
         */
        private UserRepositoryInterface $userRepository;

        /**
         * @var EmailSenderInterface email sender
         */
        private EmailSenderInterface $emailSender;

        /**
         * @var TemplateRendererInterface email template renderer
         */
        private TemplateRendererInterface $emailTemplateRenderer;

        /**
         * AccountService constructor.
         * @param UserRepositoryInterface $userRepository
         * @param EmailSenderInterface $emailSender
         * @param TemplateRendererInterface $emailTemplateRenderer
         */
        public function __construct(
            UserRepositoryInterface $userRepository,
            EmailSenderInterface $emailSender,
            TemplateRendererInterface $emailTemplateRenderer
        ) {
            $this->userRepository = $userRepository;
            $this->emailSender = $emailSender;
            $this->emailTemplateRenderer = $emailTemplateRenderer;
        }

        /**
         * @inheritDoc
         */
        public function sendNewValidationToken(int $userId): void
        {
            $user = User::load($userId);
            $newValidationToken = PasswordGenerator::generateTimeStampMd5();

            $this->userRepository->setValidationToken($userId, $newValidationToken);

            $userEmail = $user->getEmail();
            $userFullName = $user->getName('full');
            $userFirstname = $user->firstname;
            $applicationName = CONF['Application']['Name'];
            $companyEmailContact = CONF['Application']['Email'];
            $companyWebsite = CONF['Application']['Domain'];
            $subjectOfEmail = "Validation de votre compte";
            $emailContent = $this->emailTemplateRenderer->render(
                'AccountValidationEmail.html.twig',
                ['name' => $userFirstname, 'website' => $companyWebsite, 'token' => $newValidationToken]
            );

            $this->emailSender->sendHtmlEmail(
                $userEmail,
                $userFullName,
                $companyEmailContact,
                $applicationName,
                $subjectOfEmail,
                $emailContent
            );
        }
    }
}