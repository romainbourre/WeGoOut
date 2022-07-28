<?php


namespace Domain\Services\AccountService
{


    use App\Librairies\AppSettings;
    use App\Librairies\Emitter;
    use DateTime;
    use Domain\Entities\User;
    use Domain\Exceptions\BadAccountValidationTokenException;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\ResourceNotFound;
    use Domain\Exceptions\UserAlreadyExistException;
    use Domain\Exceptions\UserAlreadyValidatedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Interfaces\IEmailSender;
    use Domain\Interfaces\ITemplateRenderer;
    use Domain\Interfaces\IUserRepository;
    use Domain\Services\AccountService\Requests\LoginRequest;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use Domain\Services\AccountService\Requests\SignUpRequest;
    use Domain\Services\AccountService\Requests\ValidateAccountRequest;
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
        public function isValidAccount(string $userId): bool
        {
            $user = User::loadUserById((int)$userId);

            if (is_null($user))
            {
                throw new ResourceNotFound("user with id $userId not found.");
            }

            return $user->isValidate();
        }

        /**
         * @inheritDoc
         */
        public function login(LoginRequest $loginRequest): User
        {
            $loginRequest->valid();

            $encryptedPassword = md5($loginRequest->password);

            return User::loadUserByEmail($loginRequest->email, $encryptedPassword);
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
            $user = User::loadUserById($userId);
            $newValidationToken = Security::generateTimeStampMd5();

            $this->userRepository->setValidationToken($userId, $newValidationToken);

            $userEmail = $user->getEmail();
            $userFullName = $user->getName('full');
            $userFirstname = $user->getFirstname();
            $applicationName = CONF['Application']['Name'];
            $companyEmailContact = CONF['Application']['Email'];
            $companyWebsite = CONF['Application']['Domain'];
            $subjectOfEmail = "Validation de votre compte";
            $emailContent = $this->emailTemplateRenderer->render('AccountValidationEmail.html.twig',
                                                                 ['name' => $userFirstname, 'website' => $companyWebsite, 'token' => $newValidationToken]);

            $this->emailSender->sendHtmlEmail($userEmail, $userFullName, $companyEmailContact, $applicationName, $subjectOfEmail, $emailContent);
        }

        /**
         * @inheritDoc
         */
        public function signUp(SignUpRequest $signUpRequest): User
        {
            $this->checkSignUpRequest($signUpRequest);

            if (User::emailExist($signUpRequest->email))
            {
                throw new UserAlreadyExistException();
            }

            $encryptedPassword = md5($signUpRequest->password);

            $cleaned_data = array($signUpRequest->firstname,
                                  $signUpRequest->lastname,
                                  $signUpRequest->email,
                                  $signUpRequest->birthDate,
                                  $signUpRequest->label,
                                  $signUpRequest->postalCode,
                                  $signUpRequest->postalCode,
                                  $signUpRequest->country,
                                  $signUpRequest->longitude,
                                  $signUpRequest->latitude,
                                  $signUpRequest->placeId,
                                  $encryptedPassword,
                                  $signUpRequest->genre);

            $user = $this->userRepository->addUser($cleaned_data);

            $emitter = Emitter::getInstance();
            $emitter->emit('user.welcome', $user);

            $this->sendNewValidationToken($user->getID());

            return $user;
        }

        /**
         * @inheritDoc
         */
        public function validateAccount(int $userId, ValidateAccountRequest $validateAccountRequest): void
        {
            $userValidationToken = $this->userRepository->getValidationCode($userId);

            if (is_null($userValidationToken))
            {
                throw new UserAlreadyValidatedException();
            }

            if ($validateAccountRequest->token != $userValidationToken)
            {
                throw new BadAccountValidationTokenException();
            }

            $this->userRepository->setAccountAsValid($userId);
        }

        /**
         * Check data of sign up request
         * @param SignUpRequest $signUpRequest request
         * @throws BadArgumentException
         */
        private function checkSignUpRequest(SignUpRequest $signUpRequest)
        {
            if (!filter_var($signUpRequest->email, FILTER_VALIDATE_EMAIL))
            {
                throw new BadArgumentException("incorrect format of email");
            }

            $minimumPasswordCharacters = (new AppSettings())->getPasswordMinLength();
            if (strlen($signUpRequest->password) < $minimumPasswordCharacters)
            {
                throw new BadArgumentException("password must be have more than $minimumPasswordCharacters characters");
            }

            $birthDateFromNow = $signUpRequest->birthDate->diff(new DateTime());
            $ageOfUser = $birthDateFromNow->y;
            $minimumAgeOfUser = (new AppSettings())->getMinAgeUser();
            if ($ageOfUser < (new AppSettings())->getMinAgeUser())
            {
                throw new BadArgumentException("user must be have at lest $minimumAgeOfUser years old");
            }

            if ($signUpRequest->genre != "H" && $signUpRequest->genre != "F")
            {
                throw new BadArgumentException("genre should be only 'H' or 'F'");
            }
        }
    }
}