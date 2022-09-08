<?php


namespace Domain\Services\AccountService
{


    use Domain\Entities\User;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Interfaces\IEmailSender;
    use Domain\Interfaces\ITemplateRenderer;
    use Domain\Interfaces\IUserRepository;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use System\Librairies\Security;

    class AccountService implements IAccountService
    {
        /**
         * @var IUserRepository user repository
         */
        private IUserRepository $userRepository;

        /**
         * @var IEmailSender email sender
         */
        private IEmailSender $emailSender;

        /**
         * @var ITemplateRenderer email template renderer
         */
        private ITemplateRenderer $emailTemplateRenderer;

        /**
         * AccountService constructor.
         * @param IUserRepository $userRepository
         * @param IEmailSender $emailSender
         * @param ITemplateRenderer $emailTemplateRenderer
         */
        public function __construct(IUserRepository $userRepository, IEmailSender $emailSender, ITemplateRenderer $emailTemplateRenderer)
        {
            $this->userRepository = $userRepository;
            $this->emailSender = $emailSender;
            $this->emailTemplateRenderer = $emailTemplateRenderer;
        }

        /**
         * @inheritDoc
         */
        public function resetPassword(ResetPasswordRequest $resetPasswordRequest): void
        {
            $email = $resetPasswordRequest->email;

            if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                throw new BadArgumentException("not correct format for email");
            }

            $user = User::loadUserByEmail($email);

            $newPassword = Security::generateRandomString(6, "4");

            if ($this->userRepository->setPassword($email, md5($newPassword)))
            {
                $userFullName = $user->getName("full");
                $contactEmail = CONF['Application']['Email'];
                $companyName = CONF['Application:Name'];
                $companyWebsite = CONF['Application']['Domain'];
                $emailView = $this->emailTemplateRenderer->render('ResetPasswordEmail.html.twig', ['newPassword' => $newPassword, 'website' => $companyWebsite]);
                $subject = "Réinitialisation de mot de passe";

                $this->emailSender->sendHtmlEmail($email, $userFullName, $contactEmail, $companyName, $subject, $emailView);
            }
        }

        /**
         * @inheritDoc
         */
        public function sendNewValidationToken(int $userId): void
        {
            $user = User::load($userId);
            $newValidationToken = Security::generateTimeStampMd5();

            $this->userRepository->setValidationToken($userId, $newValidationToken);

            $userEmail = $user->getEmail();
            $userFullName = $user->getName('full');
            $userFirstname = $user->firstname;
            $applicationName = CONF['Application']['Name'];
            $companyEmailContact = CONF['Application']['Email'];
            $companyWebsite = CONF['Application']['Domain'];
            $subjectOfEmail = "Validation de votre compte";
            $emailContent = $this->emailTemplateRenderer->render('AccountValidationEmail.html.twig',
                                                                 ['name' => $userFirstname, 'website' => $companyWebsite, 'token' => $newValidationToken]);

            $this->emailSender->sendHtmlEmail($userEmail, $userFullName, $companyEmailContact, $applicationName, $subjectOfEmail, $emailContent);
        }
    }
}