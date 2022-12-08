<?php

namespace WebApp\Controllers;


use Business\Exceptions\UserNotExistException;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\Ports\UserRepositoryInterface;
use Business\UseCases\Login\LoginRequestInterface;
use Business\UseCases\Login\LoginUseCase;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\ILogger;
use System\Routing\Responses\RedirectedResponse;
use WebApp\Attributes\Page;
use WebApp\Authentication\AuthenticationConstants;
use WebApp\Exceptions\MandatoryParamMissedException;
use WebApp\Services\ToasterService\ToasterInterface;


class LoginController extends AppController
{

    public function __construct(
        private readonly ILogger $logger,
        private readonly UserRepositoryInterface $userRepository,
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly ToasterInterface $toaster
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    #[Page('login.css', 'login.js')]
    public function getView(Request $request): Response
    {
        $connectedUser = $this->authenticationGateway->getConnectedUser();
        $titleWebPage = CONF['Application']['Name'] . " - Connexion";
        $navItems = self::render('login.navitems');
        $content = self::render('login.view-login');
        $view = self::render('templates.template', compact('titleWebPage', 'navItems', 'content', 'connectedUser'));
        return $this->ok($view);
    }

    public function login(Request $request): Response
    {
        try {
            $email = $this->extractValueFromBodyOrThrow($request, 'login-user-email-field');
            $password = $this->extractValueFromBodyOrThrow($request, 'login-user-password-field');

            $loginRequest = new LoginRequestInterface($email, $password);
            $useCase = new LoginUseCase($this->userRepository, $this->authenticationGateway);
            $user = $useCase->handle($loginRequest);

            $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY] = $user->id;
            $this->logger->logTrace("user with id {$user->getID()} is now connected.");
            return RedirectedResponse::to('/');
        } catch (ValidationException $e) {
            $this->logger->logTrace($e->getMessage());
            $this->toaster->warning('l\'email saisi est mal formaté');
            return RedirectedResponse::to('/login');
        } catch (MandatoryParamMissedException $e) {
            $this->logger->logTrace($e->getMessage());
            $this->toaster->warning('des arguments sont manquant');
            return RedirectedResponse::to('/login');
        } catch (UserNotExistException $e) {
            $this->logger->logWarning($e->getMessage());
            $this->toaster->warning($e->getMessage());
            return RedirectedResponse::to('/login');
        } catch (Exception $e) {
            $this->logger->logCritical($e->getMessage());
            $this->toaster->error('Une erreur est survenue. Rééssayez plus tard.');
            return RedirectedResponse::to('/login');
        }
    }
}
