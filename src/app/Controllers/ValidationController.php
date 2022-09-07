<?php

namespace App\Controllers
{


    use App\Authentication\AuthenticationContext;
    use App\Exceptions\NotConnectedUserException;
    use Domain\Entities\Alert;
    use Domain\Exceptions\BadAccountValidationTokenException;
    use Domain\Exceptions\UserAlreadyValidatedException;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\AccountService\Requests\ValidateAccountRequest;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Logging\ILogger;
    use System\Routing\Responses\RedirectedResponse;

    /**
     * Class Validation
     * Mange validation of user account
     * @package App\Controllers
     * @author Romain Bourré
     */
    class ValidationController extends AppController
    {

        public function __construct(
            private readonly ILogger $logger,
            private readonly IAccountService $accountService,
            private readonly AuthenticationContext $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * @throws NotConnectedUserException
         */
        public function getView(Request $request): Response
        {
            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $params = $request->getQueryParams();

            $validationToken = $params['token'] ?? null;
            if (!is_null($validationToken)) {
                return $this->validAccount($validationToken);
            }

            $this->addCssStyle('css-validation.css');
            $this->addJsScript('js-validation.js');

            $userItems = null;
            $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
            $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));

            $content = self::render('validation.view-validation');

            $view = self::render('templates.template', compact('navUserDropDown', 'content', 'connectedUser'));

            return $this->ok($view);
        }

        /**
         * Send new account validation token by email
         */
        public function askNewValidationToken(): Response
        {
            try {
                $user = $this->authenticationGateway->getConnectedUser();

                if (is_null($user)) {
                    return $this->unauthorized()->withRedirectTo('/validation');
                }

                $userId = $user->getID();
                $this->accountService->sendNewValidationToken($userId);

                $this->logger->logInfo("new account validation token sent to user with id $userId");
                Alert::addAlert("Nous vous avons envoyé un email pour valider votre compte.", 1);

                return $this->ok()->withRedirectTo('/');
            } catch (Exception $e) {
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
            try {
                $user = $this->authenticationGateway->getConnectedUser();

                if (is_null($user)) {
                    return $this->unauthorized()->withRedirectTo('/validation');
                }

                $userId = $user->getID();
                $validateAccountRequest = new ValidateAccountRequest($validationToken);
                $this->accountService->validateAccount($userId, $validateAccountRequest);

                $this->logger->logInfo("user with id $userId is now validated");

                return RedirectedResponse::to('/');
            } catch (BadAccountValidationTokenException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert(
                    "Le code de validation est incorrect. Veuillez cliquez sur le lien contenu dans le dernier e-mail de validation.",
                    2
                );
                return $this->badRequest()->withRedirectTo('/');
            } catch (UserAlreadyValidatedException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert("Vous avez déjà un compte validé.", 2);
                return $this->unauthorized()->withRedirectTo('/');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                Alert::addAlert("la validation n'a pas pu s'executer correctement. Rééssayez plus tard.", 3);
                return $this->internalServerError()->withRedirectTo('/');
            }
        }
    }
}