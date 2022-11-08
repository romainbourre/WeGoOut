<?php


namespace Adapters\MySqlDatabase\Repositories
{


    use Business\Entities\User;
    use Business\Exceptions\DatabaseErrorException;
    use Business\Exceptions\ValidationException;
    use Business\Ports\UserRepositoryInterface;
    use Business\ValueObjects\Email;
    use Business\ValueObjects\FrenchDate;
    use Business\ValueObjects\Location;
    use Exception;
    use PDO;

    class UserRepository implements UserRepositoryInterface
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

        /**
         * @throws DatabaseErrorException
         */
        public function setPassword(string $userId, string $newPassword): void
        {
            $bdd = $this->databaseContext;
            $request = $bdd->prepare('UPDATE USER SET USER_PASSWORD = :pwd WHERE USER_ID = :id');
            $request->bindValue(':id', $userId);
            $request->bindValue(':pwd', $newPassword);
            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }
        }

        /**
         * @throws DatabaseErrorException
         */
        private function retrieveEmailInvitation(string $id): void
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare('SELECT * FROM GUEST_TEMP_EMAIL JOIN USER ON USER.USER_EMAIL = GUEST_TEMP_EMAIL.GUEST_EMAIL WHERE USER.USER_ID = :id');
            $request->bindValue(':id', $id);

            if (!$request->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DatabaseErrorException($errorMessage);
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
                    throw new DatabaseErrorException($errorMessage);
                }
            }

            $request3 = $bdd->prepare('DELETE FROM GUEST_TEMP_EMAIL WHERE GUEST_EMAIL = :email');
            $request3->bindValue(':email', $email);

            if (!$request3->execute())
            {
                $errorMessage = self::mapPDOErrorToString($request3->errorInfo());
                throw new DatabaseErrorException($errorMessage);
            }
        }

        /**
         * @inheritDoc
         * @throws DatabaseErrorException
         */
        public function setAccountAsValid(int $userId): void
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare('UPDATE USER SET user_validation = 1 WHERE user_id = :id');
            $request->bindValue(':id', $userId);

            if (!$request->execute())
            {
                throw new DatabaseErrorException();
            }
        }

        /**
         * @throws DatabaseErrorException
         */
        public function setValidationToken(int $userId, string $token): void
        {
            $request = $this->databaseContext->prepare('UPDATE USER SET user_validation = :code WHERE user_id = :id');
            $request->bindValue(':code', $token);
            $request->bindValue(':id', $userId);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }
        }

        /**
         * @throws DatabaseErrorException
         * @throws Exception
         */
        public function getUserByEmail(Email $email): User|null
        {
            $request = $this->databaseContext->prepare(
                'SELECT * FROM USER JOIN META_USER_CLI MUC on USER.USER_ID = MUC.USER_ID WHERE USER_EMAIL = :email'
            );
            $request->bindValue(':email', $email);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();

            if (!$result) {
                return null;
            }

            return self::mapDataToUser($result);
        }

        /**
         * @throws DatabaseErrorException
         * @throws Exception
         */
        public function getUserByEmailAndPassword(string $email, string $password): ?User
        {
            $request = $this->databaseContext->prepare(
                'SELECT * FROM USER JOIN META_USER_CLI MUC on USER.USER_ID = MUC.USER_ID WHERE USER_EMAIL = :email AND USER_PASSWORD = :password'
            );
            $request->bindValue(':email', $email);
            $request->bindValue(':password', $password);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();

            if (!$result) {
                return null;
            }

            return self::mapDataToUser($result);
        }

        /**
         * @throws Exception
         */
        private static function mapDataToUser(array $result): User
        {
            $locationOfLoadedUser = new Location(
                $result['USER_LOCATION_CP'],
                $result['USER_LOCATION_CITY'],
                (double)$result['USER_LOCATION_LAT'],
                (double)$result['USER_LOCATION_LNG']
            );
            return new User(
                id: $result['USER_ID'],
                email: $result['USER_EMAIL'],
                firstname: $result['CLI_FIRSTNAME'],
                lastname: $result['CLI_LASTNAME'],
                picture: $result['USER_PROFILE_PICTURE'],
                description: $result['CLI_DESCRIPTION'],
                birthDate: new FrenchDate(strtotime((string)$result['USER_DATE_BIRTH'])),
                location: $locationOfLoadedUser,
                validationToken: $result['USER_VALIDATION'],
                genre: $result['CLI_SEX'],
                createdAt: new FrenchDate(strtotime($result['USER_DATETIME_REGISTRATION'])),
                deletedAt: new FrenchDate(strtotime($result['USER_DATETIME_DELETE']))
            );
        }

        /**
         * @throws DatabaseErrorException
         * @throws ValidationException
         */
        public function addUserWithPassword(User $user, string $password): User
        {
            $bdd = $this->databaseContext;

            $request = $bdd->prepare(
                'INSERT INTO USER(USER_DATETIME_REGISTRATION, USER_DATE_BIRTH, USER_LOCATION_LABEL, USER_LOCATION_CP, USER_LOCATION_CITY, USER_LOCATION_COUNTRY, USER_LOCATION_PLACE_ID, USER_LOCATION_LNG, USER_LOCATION_LAT, USER_PASSWORD, USER_EMAIL, USER_TYPE, USER_VALIDATION) VALUES (:createdAt, :birthDate, :locationLabel, :locationCP, :locationCity, :locationCountry, :locationPlaceId, :locationLng, :locationLat, :userPassword, :userEmail, 0, :validationToken)'
            );

            $request->bindValue(':createdAt', date('Y-m-d H:i:s', $user->createdAt->value->getTimestamp()));
            $request->bindValue(':birthDate', $user->birthDate->value->format('Y-m-d'));
            $request->bindValue(':locationLabel', $user->location->getLabel());
            $request->bindValue(':locationCP', $user->location->postalCode);
            $request->bindValue(':locationCity', $user->location->city);
            $request->bindValue(':locationCountry', $user->location->getCountry());
            $request->bindValue(':locationPlaceId', $user->location->getGooglePlaceId());
            $request->bindValue(':locationLng', $user->location->longitude);
            $request->bindValue(':locationLat', $user->location->latitude);
            $request->bindValue(':userPassword', $password);
            $request->bindValue(':userEmail', $user->email);
            $request->bindValue(':validationToken', $user->validationToken);

            if (!$request->execute()) {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DatabaseErrorException($errorMessage);
            }

            $userId = $bdd->lastInsertId();
            $request = $bdd->prepare(
                'INSERT INTO META_USER_CLI(USER_ID, CLI_LASTNAME, CLI_FIRSTNAME, CLI_SEX) VALUES(:cliId, :cliLastname, :cliFirstname, :cliSex)'
            );
            $request->bindValue(':cliId', $userId);
            $request->bindValue(':cliLastname', $user->lastname);
            $request->bindValue(':cliFirstname', $user->firstname);
            $request->bindValue(':cliSex', $user->genre);

            if (!$request->execute()) {
                $errorMessage = self::mapPDOErrorToString($request->errorInfo());
                throw new DatabaseErrorException($errorMessage);
            }

            self::retrieveEmailInvitation($userId);

            return new User(
                id: $userId,
                email: $user->email,
                firstname: $user->firstname,
                lastname: $user->lastname,
                picture: $user->picture,
                description: $user->description,
                birthDate: $user->birthDate,
                location: $user->location,
                validationToken: $user->validationToken,
                genre: $user->genre,
                createdAt: $user->createdAt,
                deletedAt: $user->deletedAt
            );
        }

        /**
         * @throws DatabaseErrorException
         */
        public function isEmailExist(string $email): bool
        {
            $request = $this->databaseContext->prepare(
                'SELECT count(USER_ID) as userCount FROM USER WHERE user_email = :email'
            );
            $request->bindValue(':email', $email);

            if (!$request->execute()) {
                throw new DatabaseErrorException();
            }

            $result = $request->fetch();
            return isset($result['userCount']) && $result['userCount'] > 0;
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