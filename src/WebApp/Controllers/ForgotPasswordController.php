<?php

namespace WebApp\Controllers;


use Business\Exceptions\NotAuthorizedException;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\UseCases\AskNewPassword\AskNewPasswordRequest;
use Business\UseCases\AskNewPassword\AskNewPasswordUseCase;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\LoggerInterface;
use System\Routing\Responses\RedirectedResponse;
use WebApp\Attributes\Page;
use WebApp\Exceptions\MandatoryParamMissedException;
use WebApp\Services\ToasterService\ToasterInterface;


class ForgotPasswordController extends AppController
{
    public function __construct(
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly LoggerInterface                $logger,
        private readonly ToasterInterface               $toaster
    ) {
        parent::__construct();
    }

    /**
     * @throws NotAuthorizedException
     * @throws Exception
     */
    #[Page('forgot-password.css', 'forgot-password.js')]
    public function getView(): Response
    {
        $connectedUser = $this->authenticationGateway->getConnectedUser();
        if ($connectedUser != null) {
            throw new NotAuthorizedException('user should not be connected');
        }
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
            $this->toaster->success('Un e-mail vous a été envoyé, consulter votre boîte de réception');
            return RedirectedResponse::to('/login');
        } catch (MandatoryParamMissedException) {
            $this->toaster->warning('Il semble que des données soient manquantes, veuillez rééssayer');
            return $this->badRequest();
        } catch (ValidationException) {
            $this->toaster->warning('Il semble que les informations fournis soient incorrects, veuillez rééssayer');
            return $this->badRequest();
        } catch (Exception $e) {
            $this->logger->logCritical($e->getMessage());
            $this->toaster->error(
                'Un problème a été rencontré lors du changement de mot de passe. Veuillez rééssayer plus tard'
            );
            return $this->internalServerError();
        }
    }
}
