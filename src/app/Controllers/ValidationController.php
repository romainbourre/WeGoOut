<?php

namespace App\Controllers
{


    use Domain\Entities\Alert;
    use Domain\Exceptions\BadAccountValidationTokenException;
    use Domain\Exceptions\UserAlreadyValidatedException;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\AccountService\Requests\ValidateAccountRequest;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;

    /**
     * Class Validation
     * Mange validation of user account
     * @package App\Controllers
     * @author Romain Bourré
     */
    class ValidationController extends AppController
    {
        /**
         * @var ILogger logger
         */
        private ILogger $logger;

        /**
         * @var IAccountService account service
         */
        private IAccountService $accountService;

        /**
         * ValidationController constructor.
         * @param ILogger $logger
         * @param IAccountService $accountService
         */
        public function __construct(ILogger $logger, IAccountService $accountService)
        {
            parent::__construct();
            $this->accountService = $accountService;
            $this->logger = $logger;
        }

        /**
         * Display of web page
         */
        public function getView(Request $request): Response
        {
            $params = $request->getQueryParams();

            $validationToken = $params['token'] ?? null;
            if (!is_null($validationToken))
            {
                return $this->validAccount($validationToken);
            }

            $this->addCssStyle('css-validation.css');
            $this->addJsScript('js-validation.js');

            $userItems = null;
            $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
            $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu'));

            $content = self::render('validation.view-validation');

            $view = self::render('templates.template', compact('navUserDropDown', 'content'));

            return $this->ok($view);
        }

        /**
         * Send new account validation token by email
         */
        public function askNewValidationToken(): Response
        {
            try
            {

                $user = $_SESSION['USER_DATA'] ?? null;

                if (is_null($user))
                {
                    return $this->unauthorized()->withRedirectTo('/validation');
                }

                $userId = $user->getID();
                $this->accountService->sendNewValidationToken($userId);

                $this->logger->logInfo("new account validation token sent to user with id $userId");
                Alert::addAlert("Nous vous avons envoyé un email pour valider votre compte.", 1);

                return $this->ok()->withRedirectTo('/');

            }
            catch (Exception $e)
            {
                $this->logger->logCritical($e->getMessage());
                Alert::addAlert("Nous n'avons pas pu générer un nouveau lien de validation. Rééssayez plus tard.", 3);
                return $this->internalServerError()->withRedirectTo('/');
            }
        }

        /**
         * Validate user account from token
         */
        public function validAccount(string $validationToken): Response
        {
            try
            {

                $user = $_SESSION['USER_DATA'] ?? null;

                if (is_null($user))
                {
                    return $this->unauthorized()->withRedirectTo('/validation');
                }

                $userId = $user->getID();
                $validateAccountRequest = new ValidateAccountRequest($validationToken);
                $this->accountService->validateAccount($userId, $validateAccountRequest);

                $this->logger->logInfo("user with id $userId is now validated");

                return $this->ok()->withRedirectTo('/');

            }
            catch (BadAccountValidationTokenException $e)
            {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert("Le code de validation est incorrect. Veuillez cliquez sur le lien contenu dans le dernier e-mail de validation.", 2);
                return $this->badRequest()->withRedirectTo('/');
            }
            catch (UserAlreadyValidatedException $e)
            {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert("Vous avez déjà un compte validé.", 2);
                return $this->unauthorized()->withRedirectTo('/');
            }
            catch (Exception $e)
            {
                $this->logger->logCritical($e->getMessage());
                Alert::addAlert("la validation n'a pas pu s'executer correctement. Rééssayez plus tard.", 3);
                return $this->internalServerError()->withRedirectTo('/');
            }
        }
    }
}