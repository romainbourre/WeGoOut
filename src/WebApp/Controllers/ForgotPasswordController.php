<?php

namespace WebApp\Controllers;


use Business\Entities\Alert;
use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\UseCases\AskNewPassword\AskNewPasswordRequest;
use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\ILogger;
use System\Routing\Responses\RedirectedResponse;
use WebApp\Exceptions\MandatoryParamMissedException;


class ForgotPasswordController extends AppController
{
    public function __construct(
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly ILogger $logger
    ) {
        parent::__construct();
    }

    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    public function getView(): Response
    {
        $connectedUser = $this->authenticationGateway->getConnectedUser();
        if ($connectedUser != null) {
            throw new NotAuthorizedException('user should not be connected');
        }

        $this->addCssStyle('css-forgotpwd.css');
        $this->addJsScript('js-forgotpwd.js');
        $navItems = self::render('forgotpwd.navitems');
        $content = self::render('forgotpwd.view-forgotpwd');
        $view = self::render('templates.template', compact('navItems', 'content', 'connectedUser'));
        return $this->ok($view);
    }

    public function resetPassword(Request $request, AskNewPasswordUseCase $useCase): Response
    {
        try {
            $emailOfUser = $this->extractValueFromBodyOrThrow($request, 'forgot-password-email-field');
            $resetPasswordRequest = new AskNewPasswordRequest($emailOfUser);
            $useCase->handle($resetPasswordRequest);
            alert::addAlert('Un e-mail vous a été envoyé, consulter votre boîte de réception', 1);
            return RedirectedResponse::to('/login');
        } catch (MandatoryParamMissedException) {
            alert::addAlert('Il semble que des données soient manquantes, veuillez rééssayer', 2);
            return $this->badRequest();
        } catch (ValidationException) {
            alert::addAlert('Il semble que les informations fournis soient incorrects, veuillez rééssayer', 2);
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
