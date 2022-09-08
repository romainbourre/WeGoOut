<?php

namespace App\Controllers
{


    use App\Authentication\AuthenticationConstants;
    use App\Authentication\AuthenticationContext;
    use App\Exceptions\MandatoryParamMissedException;
    use Domain\Entities\Alert;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\ValidationException;
    use Domain\Interfaces\IUserRepository;
    use Domain\Services\AccountService\Requests\LoginRequest;
    use Domain\UseCases\Login\LoginUseCase;
    use Exception;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;
    use System\Logging\ILogger;
    use System\Routing\Responses\RedirectedResponse;

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
            private readonly IUserRepository $userRepository,
            private readonly AuthenticationContext $authenticationGateway
        ) {
            parent::__construct();
        }

        /**
         * View display of login page
         * @param Request $request
         * @return Response
         * @throws Exception
         */
        public function getView(Request $request): Response
        {
            $this->addCssStyle('css-login.css');
            $this->addJsScript('js-login.js');

            $connectedUser = $this->authenticationGateway->getConnectedUser();
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
                $email = $this->extractValueFromBodyOrThrow($request, 'login-user-email-field');
                $password = $this->extractValueFromBodyOrThrow($request, 'login-user-password-field');

                $loginRequest = new LoginRequest($email, $password);
                $useCase = new LoginUseCase($this->userRepository, $this->authenticationGateway);
                $user = $useCase->handle($loginRequest);

                $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY] = $user->id;
                $this->logger->logTrace("user with id {$user->getID()} is now connected.");
                return RedirectedResponse::to('/');
            } catch (ValidationException) {
                Alert::addAlert("l'email saisi est mal formaté");
                return RedirectedResponse::to('/login');
            } catch (MandatoryParamMissedException $e) {
                $this->logger->logTrace($e->getMessage());
                return RedirectedResponse::to('/login');
            } catch (UserNotExistException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert($e->getMessage(), 2);
                return RedirectedResponse::to('/login');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage());
                Alert::addAlert('Une erreur est survenue. Rééssayez plus tard.', 3);
                return RedirectedResponse::to('/login');
            }
        }
    }
}