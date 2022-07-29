<?php

namespace App\Controllers
{

    use App\Authentication\AuthenticationConstants;
    use App\Authentication\AuthenticationContext;
    use App\Exceptions\NotConnectedUserException;
    use DateTime;
    use Domain\Exceptions\BadArgumentException;
    use Domain\Exceptions\UserAlreadyExistException;
    use Domain\Entities\Alert;
    use Domain\Services\AccountService\IAccountService;
    use Domain\Services\AccountService\Requests\SignUpRequest;
    use Exception;
    use System\Logging\ILogger;
    use Slim\Psr7\Request;
    use Slim\Psr7\Response;

    /**
     * Manage register page
     * @package App\Controllers
     * @author Romain Bourré
     */
    class SignUpController extends AppController
    {

        /**
         * SignUpController constructor.
         * @param ILogger $logger
         * @param IAccountService $accountService
         */
        public function __construct(
            private readonly AuthenticationContext $authenticationGateway,
            private readonly ILogger $logger,
            private readonly IAccountService $accountService
        ) {
            parent::__construct();
        }

        /**
         * @throws NotConnectedUserException
         */
        public function getView(Request $request): Response
        {
            // LOAD CSS AND JS SCRIPT FILES
            $this->addCssStyle('css-register.css');
            $this->addJsScript('js-register.js');

            $connectedUser = $this->authenticationGateway->getConnectedUserOrThrow();
            $titleWebPage = CONF['Application']['Name'] . " - Inscription";
            $navItems = self::render('register.navitems');

            $content = self::render('register.view-register');

            $view = self::render('templates.template', compact('titleWebPage', 'navItems', 'content', 'connectedUser'));

            return $this->ok($view);
        }

        /**
         * Save data form for a new user after check data (LEVEL 2)
         * @param Request $request
         * @return Response
         */
        public function signUp(Request $request): Response
        {
            try {
                $this->logger->logDebug("start to sign up user");

                if (!$cleaned_data = $this->getData($request)) {
                    return $this->badRequest()->withRedirectTo('/sign-up');
                }

                list(
                    $registration_first_name,
                    $registration_last_name,
                    $registration_email,
                    $registration_birth_date,
                    $registration_location_label,
                    $registration_location_postal_code,
                    $registration_location_city,
                    $registration_location_country,
                    $registration_location_longitude,
                    $registration_location_latitude,
                    $registration_location_place_id,
                    $registration_password,
                    $registration_sex
                    ) = $cleaned_data;

                $birthDate = (new DateTime())->setTimestamp($registration_birth_date);
                $signUpRequest = new SignUpRequest(
                    $registration_first_name,
                    $registration_last_name,
                    $registration_email,
                    $birthDate,
                    $registration_location_label,
                    $registration_location_postal_code,
                    $registration_location_city,
                    $registration_location_country,
                    $registration_location_longitude,
                    $registration_location_latitude,
                    $registration_location_place_id,
                    $registration_password,
                    $registration_sex
                );

                $this->logger->logDebug("start sign up");
                $user = $this->accountService->signUp($signUpRequest);

                $_SESSION[AuthenticationConstants::USER_DATA_SESSION_KEY] = $user;

                $this->logger->logInfo("user with email $registration_email} signed up.");

                return $this->ok()->withRedirectTo('/');
            } catch (BadArgumentException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert('Certaines données du formulaire semblent incorrect. Rééssayez.', 2);
                return $this->badRequest()->withRedirectTo('/sign-up');
            } catch (UserAlreadyExistException $e) {
                $this->logger->logWarning($e->getMessage());
                Alert::addAlert('Cette adresse e-mail est déjà utilisé pour un autre compte.', 2);
                return $this->badRequest()->withRedirectTo('/sign-up');
            } catch (Exception $e) {
                $this->logger->logCritical($e->getMessage(), $e);
                Alert::addAlert('Une erreur est survenue lors de l\'inscription. Veuillez rééssayer plus tard', 3);
                return $this->internalServerError()->withRedirectTo('/sign-up');
            }
        }

        /**
         * Get form data for a new user and check data (LEVEL 1)
         * @param Request $request
         * @return array|null
         */
        public function getData(Request $request): ?array
        {
            $params = $request->getParsedBody();

            $run = true;

            if ( // CHECK IF DATA FORM EXIST
                isset($params['registration-user-lastName-field'])
                && isset($params['registration-user-firstName-field'])
                && isset($params['registration-user-birthDate-field'])
                && isset($params['registration-user-sex-select'])
                && isset($params['registration-user-email-field'])
                && isset($params['registration-user-password-field'])
                && isset($params['registration-user-location-field'])
                && isset($params['registration-user-placeId-hidden'])
                && isset($params['registration-user-latitude-hidden'])
                && isset($params['registration-user-longitude-hidden'])
            ) {
                if ( // CHECK IF DATA FORM IS NO EMPTY
                    !empty($params['registration-user-lastName-field'])
                    && !empty($params['registration-user-firstName-field'])
                    && !empty($params['registration-user-birthDate-field'])
                    && !empty($params['registration-user-sex-select'])
                    && !empty($params['registration-user-email-field'])
                    && !empty($params['registration-user-password-field'])
                    && !empty($params['registration-user-location-field'])
                    && !empty($params['registration-user-placeId-hidden'])
                    && !empty($params['registration-user-latitude-hidden'])
                    && !empty($params['registration-user-longitude-hidden'])
                ) {
                    $registration_first_name = htmlspecialchars($params['registration-user-firstName-field']);
                    $registration_last_name = htmlspecialchars($params['registration-user-lastName-field']);
                    $registration_email = htmlspecialchars($params['registration-user-email-field']);
                    $registration_birth_date = htmlspecialchars($params['registration-user-birthDate-field']);
                    $registration_location_label = htmlspecialchars($params['registration-user-location-field']);
                    $registration_location_postal_code = htmlspecialchars(
                        $params['registration-user-postalCode-hidden']
                    );
                    $registration_location_city = htmlspecialchars($params['registration-user-city-hidden']);
                    $registration_location_country = htmlspecialchars($params['registration-user-country-hidden']);
                    $registration_location_longitude = (float)htmlspecialchars(
                        $params['registration-user-longitude-hidden']
                    );
                    $registration_location_latitude = (float)htmlspecialchars(
                        $params['registration-user-latitude-hidden']
                    );
                    $registration_location_place_id = htmlspecialchars($params['registration-user-placeId-hidden']);
                    $registration_password = htmlspecialchars($params['registration-user-password-field']);
                    $registration_sex = htmlspecialchars($params['registration-user-sex-select']);

                    // CHECK BIRTH DATE
                    if (preg_match(
                            '#^([0-9]{2})([/-])([0-9]{2})\2([0-9]{4})$#',
                            $registration_birth_date,
                            $d
                        ) && checkdate($d[3], $d[1], $d[4])) {
                        $registration_birth_date = mktime(0, 0, 0, $d[3], $d[1], $d[4]);
                    } else {
                        $run = false;
                    }

                    // CHECK A CITY SEIZURE
                    if (empty($registration_location_label) || empty($registration_location_place_id) || empty($registration_location_longitude) || empty($registration_location_latitude)) {
                        $run = false;
                    }

                    if ($run) {
                        return array(
                            $registration_first_name,
                            $registration_last_name,
                            $registration_email,
                            $registration_birth_date,
                            $registration_location_label,
                            $registration_location_postal_code,
                            $registration_location_city,
                            $registration_location_country,
                            $registration_location_longitude,
                            $registration_location_latitude,
                            $registration_location_place_id,
                            $registration_password,
                            $registration_sex
                        );
                    }

                    Alert::addAlert('Il semble que les informations fournis soient incorrects, veuillez rééssayer', 2);

                    return null;
                }

                Alert::addAlert('Il semble que des données soient manquantes, veuillez rééssayer', 2);
            }

            return null;
        }
    }
}