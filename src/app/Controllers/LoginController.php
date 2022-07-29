<?php

namespace App\Controllers
{


    use App\Authentication\AuthenticationConstants;
    use App\Authentication\AuthenticationContext;
    use App\Exceptions\NotConnectedUserException;
    use Domain\Exceptions\UserIncorrectPasswordException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Entities\Alert;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\AccountService\Requests\LoginRequest;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;

    /**
     * Class Login
     * Display and manage login page
     * @package App\Controllers
     * @author Romain Bourré
     */
    class LoginController extends AppController
    {

        public function __construct(
            private readonly ILogger $logger,
            private readonly IAccountService $accountService,
            private readonly AuthenticationContext $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * View display of login page
         * @throws NotConnectedUserException
         */
        public function getView(Request $request): Response
        {
            $this->addCssStyle('css-login.css');
            $this->addJsScript('js-login.js');

            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $titleWebPage = CONF['Application']['Name'] . " - Connexion";

            $navItems = self::render('login.navitems');

            $content = self::render('login.view-login');

            $view = self::render('templates.template', compact('titleWebPage', 'navItems', 'content', 'connectedUser'));

            return $this->ok($view);
        }

        /**
         * Check the data login of user and connect him.
         * If error detected, a error message is display in the browser
         * @param Request $request
         * @return Response
         */
        public function login(Request $request): Response
        {
            try {
                $connectedUser = $this->authenticationGateway;

                if (!is_null($connectedUser)) {
                    return $this->unauthorized();
                }

                if (!$cleaned_data = $this->getData($request)) {
                    return $this->badRequest();
                }

                list($email, $password) = $cleaned_data;

                $loginRequest = new LoginRequest($email, $password);
                $user = $this->accountService->login($loginRequest);

                $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY] = $user;

                $this->logger->logTrace("user with id {$user->getID()} is now connected.");

                return $this->ok()->withRedirectTo('/');
            } catch (UserNotExistException|UserIncorrectPasswordException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert($e->getMessage(), 2);
                return $this->badRequest()->withRedirectTo('/login');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                Alert::addAlert('Une erreur est survenue. Rééssayez plus tard.', 3);
                return $this->internalServerError()->withRedirectTo('/login');
            }
        }

        /**
         * Get post data of form login page and check validity
         * @param Request $request
         * @return array|null
         */
        private function getData(Request $request): ?array
        {
            $params = $request->getParsedBody();

            if (isset($params['login-user-email-field']) && isset($params['login-user-password-field'])) {
                if (!empty($params['login-user-email-field']) && !empty($params['login-user-password-field'])) {
                    $login_email = htmlspecialchars($params['login-user-email-field']);
                    $login_password = htmlspecialchars($params['login-user-password-field']);

                    return array($login_email, $login_password);
                }

                Alert::addAlert('Il semble que des données soient manquantes, veuillez rééssayer', 2);
            }

            return null;
        }
    }
}