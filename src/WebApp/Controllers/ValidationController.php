<?php

namespace WebApp\Controllers
{


    use Business\Exceptions\IncorrectValidationTokenException;
    use Business\Exceptions\UserAlreadyValidatedException;
    use Business\Ports\AuthenticationContextInterface;
    use Business\Services\AccountService\IAccountService;
    use Business\UseCases\ValidateUserAccount\ValidateUserAccountRequest;
    use Business\UseCases\ValidateUserAccount\ValidateUserAccountUseCase;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Logging\ILogger;
    use System\Routing\Responses\RedirectedResponse;
    use WebApp\Attributes\Page;
    use WebApp\Exceptions\NotConnectedUserException;
    use WebApp\Services\ToasterService\ToasterInterface;

    class ValidationController extends AppController
    {

        public function __construct(
            private readonly ILogger $logger,
            private readonly IAccountService $accountService,
            private readonly AuthenticationContextInterface $authenticationGateway,
            private readonly ToasterInterface $toaster
        ) {
            parent::__construct();
        }

        /**
         * @throws NotConnectedUserException
         * @throws Exception
         */
        #[Page('validation.css', 'validation.js')]
        public function index(Request $request, ValidateUserAccountUseCase $useCase): Response
        {
            $validationToken = $this->extractValueFromQuery($request, 'token');
            if (!is_null($validationToken)) {
                return $this->validateAccountOfUser($validationToken, $useCase);
            }

            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();

            $userItems = null;
            $userMenu = $this->render('templates.nav-usermenu', compact('userItems'));
            $navUserDropDown = $this->render('templates.nav-userdropdown', compact('userMenu', 'connectedUser'));

            $content = self::render('validation.view-validation');

            $view = self::render('templates.template', compact('navUserDropDown', 'content', 'connectedUser'));

            return $this->ok($view);
        }

        private function validateAccountOfUser(string $validationToken, ValidateUserAccountUseCase $useCase): Response
        {
            try {
                $validateAccountRequest = new ValidateUserAccountRequest($validationToken);
                $useCase->handle($validateAccountRequest);
                $this->logger->logInfo("user is now validated");
                return RedirectedResponse::to('/');
            } catch (IncorrectValidationTokenException $e) {
                $this->logger->logWarning($e->getMessage());
                $this->toaster->warning(
                    'Le code de validation est incorrect. Veuillez cliquez sur le lien contenu dans le dernier e-mail de validation.'
                );
                return RedirectedResponse::to('/');
            } catch (UserAlreadyValidatedException $e) {
                $this->logger->logWarning($e->getMessage());
                $this->toaster->warning('Vous avez déjà un compte validé.');
                return RedirectedResponse::to('/');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                $this->toaster->error('la validation n\'a pas pu s\'exécuter correctement. Réessayez plus tard.');
                return RedirectedResponse::to('/');
            }
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
                $this->toaster->success('Nous vous avons envoyé un email pour valider votre compte.');
                return $this->ok()->withRedirectTo('/');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                $this->toaster->error(
                    'Nous n\'avons pas pu générer un nouveau lien de validation. Rééssayez plus tard.'
                );
                return $this->internalServerError()->withRedirectTo('/');
            }
        }
    }
}
