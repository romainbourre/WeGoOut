<?php

namespace App\Controllers
{


    use Domain\Entities\Alert;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\AccountService\Requests\ResetPasswordRequest;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;

    class ForgotPasswordController extends AppController
    {
        private ILogger $logger;
        private IAccountService $accountService;

        /**
         * ForgotPasswordController constructor.
         * @param ILogger $logger
         * @param IAccountService $accountService
         */
        public function __construct(ILogger $logger, IAccountService $accountService)
        {
            parent::__construct();
            $this->logger = $logger;
            $this->accountService = $accountService;
        }

        /**
         * Display forgot password page
         */
        public function getView(): Response
        {

            $this->addCssStyle('css-forgotpwd.css');
            $this->addJsScript('js-forgotpwd.js');

            $navItems = self::render('forgotpwd.navitems');

            $content = self::render('forgotpwd.view-forgotpwd');

            $view = self::render('templates.template', compact( 'navItems', 'content'));

            return $this->ok($view);
        }

        /**
         * Get data of forgot password form
         * @return string|null e-mail's user or null
         */
        private function getData(Request $request): ?string
        {
            $params = $request->getParsedBody();

            if (!isset($params['forgot-password-email-field']))
            {
                return null;
            }

            $emailOfUser = htmlspecialchars($params['forgot-password-email-field']);

            if (empty($emailOfUser))
            {
                echo "<script>Materialize.toast('Il semble que des données soient manquantes, veuillez rééssayer', 4000, 'red-text text-accent-2')</script>";
                return null;
            }

            return $emailOfUser;
        }

        /**
         * Reset user's password
         */
        public function resetPassword(Request $request): Response
        {
            try
            {
                $emailOfUser = $this->getData($request);

                if (is_null($emailOfUser))
                {
                    return $this->badRequest();
                }

                $resetPasswordRequest = new ResetPasswordRequest($emailOfUser);
                $this->accountService->resetPassword($resetPasswordRequest);

                alert::addAlert('Un e-mail vous a été envoyé, consulter votre boîte de réception', 1);
                return $this->ok()->withRedirectTo('/login');

            }
            catch (BadArgumentException $e)
            {
                echo "<script>Materialize.toast('Il semble que les informations fournis soient incorrects, veuillez rééssayer', 4000, 'red-text text-accent-2')</script>";
                return $this->badRequest();
            }
            catch (UserNotExistException $e)
            {
                $this->logger->logError($e->getMessage());
                alert::addAlert('Un problème a été rencontré lors du changement de mot de passe. Veuillez rééssayer plus tard', 3);
                return $this->badRequest();
            }
            catch (Exception $e)
            {
                $this->logger->logCritical($e->getMessage());
                alert::addAlert('Un problème a été rencontré lors du changement de mot de passe. Veuillez rééssayer plus tard', 3);
                return $this->internalServerError();
            }
        }
    }
}