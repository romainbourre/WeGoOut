<?php


namespace Infrastructure\MySqlDatabase\Repositories
{


    use Domain\Entities\User;
    use Domain\Exceptions\DataNotSavedException;
    use Domain\Exceptions\UserDeletedException;
    use Domain\Exceptions\UserNotExistException;
    use Domain\Exceptions\UserSignaledException;
    use Domain\Interfaces\IUserRepository;
    use PDO;

    class UserRepository implements IUserRepository
    {
        private PDO $databaseContext;

        /**
         * UserRepository constructor.
         * @param PDO $databaseContext
         */
        public function __construct(PDO $databaseContext)
        {
            $this->databaseContext = $databaseContext;
        }

        public function setPassword(string $email, string $pwd): bool
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare('UPDATE USER SET user_password = :pwd WHERE user_email = :email');
            $request->bindValue(':email', $email);
            $request->bindValue(':pwd', $pwd);

            return $request->execute();
        }

        /**
         * @inheritDoc
         */
        public function addUser(array $data): User
        {
            try
            {
                $bdd = $this->databaseContext;

                list($registration_first_name,
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
                    $registration_sex) = $data;

                $request = $bdd->prepare('INSERT INTO USER(USER_DATETIME_REGISTRATION, USER_DATE_BIRTH, USER_LOCATION_LABEL, USER_LOCATION_CP, USER_LOCATION_CITY, USER_LOCATION_COUNTRY, USER_LOCATION_PLACE_ID, USER_LOCATION_LNG, USER_LOCATION_LAT, USER_PASSWORD, USER_EMAIL, USER_TYPE) VALUES (sysdate(), :birthDate, :locationLabel, :locationCP, :locationCity, :locationCountry, :locationPlaceId, :locationLng, :locationLat, :userPassword, :userEmail, 0)');

                $request->bindValue(':birthDate', $registration_birth_date->format('Y-m-d'));
                $request->bindValue(':locationLabel', $registration_location_label);
                $request->bindValue(':locationCP', $registration_location_postal_code);
                $request->bindValue(':locationCity', $registration_location_city);
                $request->bindValue(':locationCountry', $registration_location_country);
                $request->bindValue(':locationPlaceId', $registration_location_place_id);
                $request->bindValue(':locationLng', $registration_location_longitude);
                $request->bindValue(':locationLat', $registration_location_latitude);
                $request->bindValue(':userPassword', $registration_password);
                $request->bindValue(':userEmail', $registration_email);

                if (!$request->execute())
                {
                    $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                    throw new DataNotSavedException($errorMessage);
                }

                $lastId = $bdd->lastInsertId();

                $request = $bdd->prepare('INSERT INTO META_USER_CLI(USER_ID, CLI_LASTNAME, CLI_FIRSTNAME, CLI_SEX) VALUES(:cliId, :cliLastname, :cliFirstname, :cliSex)');
                $request->bindValue(':cliId', $lastId);
                $request->bindValue(':cliLastname', $registration_last_name);
                $request->bindValue(':cliFirstname', $registration_first_name);
                $request->bindValue(':cliSex', $registration_sex);

                if (!$request->execute())
                {
                    $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                    throw new DataNotSavedException($errorMessage);
                }

                self::retrieveEmailInvitation($lastId);

                return User::loadUserById($lastId);
            }
            catch (UserNotExistException | UserSignaledException | UserDeletedException $e) {
                throw new DataNotSavedException($e->getMessage());
            }
        }

        /**
         * @throws DataNotSavedException
         */
        private function retrieveEmailInvitation(string $id): void
        {

            $bdd = $this->databaseContext;

            $request = $bdd->prepare('SELECT * FROM GUEST_TEMP_EMAIL JOIN USER ON USER.USER_EMAIL = GUEST_TEMP_EMAIL.GUEST_EMAIL WHERE USER.USER_ID = :id');
            $request->bindValue(':id', $id);

            if (!$request->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DataNotSavedException($errorMessage);
            }

            $email = "";

            while ($result = $request->fetch())
            {
                if (empty($email)) $email = $result['USER_EMAIL'];

                $request2 = $bdd->prepare('INSERT INTO GUEST(EVENT_ID, USER_ID, GUEST_DATETIME_SEND, GUEST_DATETIME_DELETE) VALUES (:eventId, :userId, :send, :delete)');
                $request2->bindValue(':eventId', $result['EVENT_ID']);
                $request2->bindValue(':userId', $result['USER_ID']);
                $request2->bindValue(':send', $result['GUEST_DATETIME_SEND']);
                $request2->bindValue(':delete', $result['GUEST_DATETIME_DELETE']);

                if (!$request2->execute())
                {
                    $errorMessage = self::mapPDOErrorToString($request2->errorInfo());
                    throw new DataNotSavedException($errorMessage);
                }
            }

            $request3 = $bdd->prepare('DELETE FROM GUEST_TEMP_EMAIL WHERE GUEST_EMAIL = :email');
            $request3->bindValue(':email', $email);

            if (!$request3->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request3->errorInfo());
                throw new DataNotSavedException($errorMessage);
            }
        }

        public function getValidationCode(int $id)
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare('SELECT user_validation FROM USER WHERE user_id = :id');
            $request->bindValue(':id', $id);

            if ($request->execute()) return ($request->fetch())['user_validation'];
            else return false;
        }

        /**
         * @inheritDoc
         */
        public function setAccountAsValid(int $userId): void
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare('UPDATE USER SET user_validation = 1 WHERE user_id = :id');
            $request->bindValue(':id', $userId);

            if (!$request->execute())
            {
                throw new DataNotSavedException();
            }
        }

        /**
         * @inheritDoc
         */
        public function setValidationToken(int $userId, string $token): void
        {
            $request = $this->databaseContext->prepare('UPDATE USER SET user_validation = :code WHERE user_id = :id');
            $request->bindValue(':code', $token);
            $request->bindValue(':id', $userId);

            if (!$request->execute())
            {
                throw new DataNotSavedException();
            }
        }

        /**
         * Make PDO array errors to string
         * @param array $pdoError
         * @return string
         */
        private static function mapPDOErrorToString(array $pdoError): string
        {
            $errorString = '';
            foreach ($pdoError as $error)
            {
                $errorString .= "$error ";
            }

            return $errorString;
        }
    }
}