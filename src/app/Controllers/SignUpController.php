<?php

namespace App\Controllers;

use App\Authentication\AuthenticationConstants;
use App\Authentication\AuthenticationContext;
use App\Exceptions\MandatoryParamMissedException;
use Domain\Entities\Alert;
use Domain\Exceptions\UserAlreadyExistException;
use Domain\Exceptions\ValidationException;
use Domain\UseCases\SignUp\SignUpRequest;
use Domain\UseCases\SignUp\SignUpUseCase;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\ILogger;
use System\Routing\Responses\RedirectedResponse;


class SignUpController extends AppController
{

    public function __construct(
        private readonly AuthenticationContext $authenticationGateway,
        private readonly ILogger $logger
    ) {
        parent::__construct();
    }

    public function getView(Request $request): Response
    {
        // LOAD CSS AND JS SCRIPT FILES
        $this->addCssStyle('css-register.css');
        $this->addJsScript('js-register.js');

        $connectedUser = $this->authenticationGateway->getConnectedUser();
        $titleWebPage = CONF['Application']['Name'] . " - Inscription";
        $navItems = self::render('register.navitems');

        $content = self::render('register.view-register');

        $view = self::render('templates.template', compact('titleWebPage', 'navItems', 'content', 'connectedUser'));

        return $this->ok($view);
    }

    /**
     * @param Request $request
     * @param SignUpUseCase $useCase
     * @return Response
     */
    public function signUp(Request $request, SignUpUseCase $useCase): Response
    {
        try {
            $signUpRequest = $this->extractSignUpRequest($request);
            $user = $useCase->handle($signUpRequest);
            $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY] = $user->id;
            $this->logger->logInfo("user with email $user->email signed up.");
            return RedirectedResponse::to('/');
        } catch (MandatoryParamMissedException|ValidationException $e) {
            $this->logger->logWarning($e->getMessage());
            Alert::addAlert('Certaines données du formulaire semblent incorrect. Rééssayez.', 2);
            return RedirectedResponse::to('/sign-up');
        } catch (UserAlreadyExistException $e) {
            $this->logger->logWarning($e->getMessage());
            Alert::addAlert('Cette adresse e-mail est déjà utilisé pour un autre compte.', 2);
            return RedirectedResponse::to('/sign-up');
        } catch (Exception $e) {
            $this->logger->logCritical($e->getMessage(), $e);
            Alert::addAlert('Une erreur est survenue lors de l\'inscription. Veuillez rééssayer plus tard', 3);
            return RedirectedResponse::to('/sign-up');
        }
    }

    /**
     * @throws MandatoryParamMissedException
     */
    private function extractSignUpRequest(Request $request): SignUpRequest
    {
        return new SignUpRequest(
            firstname: $this->extractValueFromRequestOrThrow($request, 'registration-user-firstName-field'),
            lastname: $this->extractValueFromRequestOrThrow($request, 'registration-user-lastName-field'),
            email: $this->extractValueFromRequestOrThrow($request, 'registration-user-email-field'),
            birthDate: $this->extractValueFromRequestOrThrow($request, 'registration-user-birthDate-field'),
            label: $this->extractValueFromRequestOrThrow($request, 'registration-user-location-field'),
            postalCode: $this->extractValueFromRequestOrThrow($request, 'registration-user-postalCode-hidden'),
            city: $this->extractValueFromRequestOrThrow($request, 'registration-user-city-hidden'),
            country: $this->extractValueFromRequestOrThrow($request, 'registration-user-country-hidden'),
            longitude: $this->extractValueFromRequestOrThrow($request, 'registration-user-longitude-hidden'),
            latitude: $this->extractValueFromRequestOrThrow($request, 'registration-user-latitude-hidden'),
            placeId: $this->extractValueFromRequestOrThrow($request, 'registration-user-placeId-hidden'),
            password: $this->extractValueFromRequestOrThrow($request, 'registration-user-password-field'),
            genre: $this->extractValueFromRequestOrThrow($request, 'registration-user-sex-select')
        );
    }
}
