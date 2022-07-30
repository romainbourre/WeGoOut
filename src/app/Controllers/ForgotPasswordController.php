<?php

namespace App\Controllers
{


    use App\Authentication\AuthenticationContext;
    use App\Exceptions\NotConnectedUserException;
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


        public function __construct(
            private readonly AuthenticationContext $authenticationGateway,
            private readonly ILogger $logger,
            private readonly IAccountService $accountService
        ) {
            parent::__construct();
        }

        /**
         * Display forgot password page
         * @throws NotConnectedUserException
         */
        public function getView(): Response
        {
            $this->addCssStyle('css-forgotpwd.css');
            $this->addJsScript('js-forgotpwd.js');

            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $navItems = self::render('forgotpwd.navitems');

            $content = self::render('forgotpwd.view-forgotpwd');

            $view = self::render('templates.template', compact('navItems', 'content', 'connectedUser'));

            return $this->ok($view);
        }

        /**
         * Get data of forgot password form
         * @return string|null e-mail's user or null
         */
        private function getData(Request $request): ?string
        {
            $params = $request->getParsedBody();

            if (!isset($params['forgot-password-email-field'])) {
                return null;
            }

            $emailOfUser = htmlspecialchars($params['forgot-password-email-field']);

            if (empty($emailOfUser)) {
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
            try {
                $emailOfUser = $this->getData($request);

                if (is_null($emailOfUser)) {
                    return $this->badRequest();
                }

                $resetPasswordRequest = new ResetPasswordRequest($emailOfUser);
                $this->accountService->resetPassword($resetPasswordRequest);

                alert::addAlert('Un e-mail vous a été envoyé, consulter votre boîte de réception', 1);
                return $this->ok()->withRedirectTo('/login');
            } catch (BadArgumentException $e) {
                echo "<script>Materialize.toast('Il semble que les informations fournis soient incorrects, veuillez rééssayer', 4000, 'red-text text-accent-2')</script>";
                return $this->badRequest();
            } catch (UserNotExistException $e) {
                $this->logger->logError($e->getMessage());
                alert::addAlert(
                    'Un problème a été rencontré lors du changement de mot de passe. Veuillez rééssayer plus tard',
                    3
                );
                return $this->badRequest();
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                alert::addAlert(
                    'Un problème a été rencontré lors du changement de mot de passe. Veuillez rééssayer plus tard',
                    3
                );
                return $this->internalServerError();
            }
        }
    }
}