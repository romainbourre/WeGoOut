<?php

namespace WebApp\Controllers;

use Business\Exceptions\UserAlreadyExistException;
use Business\Exceptions\ValidationException;
use Business\Ports\AuthenticationContextInterface;
use Business\UseCases\SignUp\SignUpRequest;
use Business\UseCases\SignUp\SignUpUseCase;
use Exception;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
use System\Logging\LoggerInterface;
use System\Routing\Responses\RedirectedResponse;
use WebApp\Attributes\Page;
use WebApp\Authentication\AuthenticationConstants;
use WebApp\Exceptions\MandatoryParamMissedException;
use WebApp\Services\ToasterService\ToasterInterface;


class SignUpController extends AppController
{

    public function __construct(
        private readonly AuthenticationContextInterface $authenticationGateway,
        private readonly LoggerInterface                $logger,
        private readonly ToasterInterface               $toaster
    )
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    #[Page('signup.css', 'signup.js')]
    public function getView(Request $request): Response
    {
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
            $this->toaster->warning('Certaines données du formulaire semblent incorrect. Rééssayez.');
            return RedirectedResponse::to('/sign-up');
        } catch (UserAlreadyExistException $e) {
            $this->logger->logWarning($e->getMessage());
            $this->toaster->warning('Cette adresse e-mail est déjà utilisé pour un autre compte.');
            return RedirectedResponse::to('/sign-up');
        } catch (Exception $e) {
            $this->logger->logCritical($e->getMessage(), $e);
            $this->toaster->error('Une erreur est survenue lors de l\'inscription. Veuillez rééssayer plus tard');
            return RedirectedResponse::to('/sign-up');
        }
    }

    /**
     * @throws MandatoryParamMissedException
     */
    private function extractSignUpRequest(Request $request): SignUpRequest
    {
        return new SignUpRequest(
            firstname: $this->extractValueFromBodyOrThrow($request, 'registration-user-firstName-field'),
            lastname: $this->extractValueFromBodyOrThrow($request, 'registration-user-lastName-field'),
            email: $this->extractValueFromBodyOrThrow($request, 'registration-user-email-field'),
            birthDate: $this->extractValueFromBodyOrThrow($request, 'registration-user-birthDate-field'),
            label: $this->extractValueFromBodyOrThrow($request, 'registration-user-location-field'),
            postalCode: $this->extractValueFromBodyOrThrow($request, 'registration-user-postalCode-hidden'),
            city: $this->extractValueFromBodyOrThrow($request, 'registration-user-city-hidden'),
            country: $this->extractValueFromBodyOrThrow($request, 'registration-user-country-hidden'),
            longitude: $this->extractValueFromBodyOrThrow($request, 'registration-user-longitude-hidden'),
            latitude: $this->extractValueFromBodyOrThrow($request, 'registration-user-latitude-hidden'),
            placeId: $this->extractValueFromBodyOrThrow($request, 'registration-user-placeId-hidden'),
            password: $this->extractValueFromBodyOrThrow($request, 'registration-user-password-field'),
            genre: $this->extractValueFromBodyOrThrow($request, 'registration-user-sex-select')
        );
    }
}
