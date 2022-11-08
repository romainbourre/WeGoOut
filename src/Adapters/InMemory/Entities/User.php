<?php


namespace Adapters\InMemory\Entities
{

    use Business\Entities\User as DomainUser;
    use Business\Exceptions\ValidationException;

    class User extends DomainUser
    {
        public readonly string $password;

        /**
         * @throws ValidationException
         */
        public static function from(DomainUser $user, string $password): User
        {
            $user = new User(
                id: $user->id,
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
            $user->password = $password;
            return $user;
        }

        /**
         * @throws ValidationException
         */
        public function toDomainUser(): DomainUser
        {
            return new DomainUser(
                id: $this->id,
                email: $this->email,
                firstname: $this->firstname,
                lastname: $this->lastname,
                picture: $this->picture,
                description: $this->description,
                birthDate: $this->birthDate,
                location: $this->location,
                validationToken: $this->validationToken,
                genre: $this->genre,
                createdAt: $this->createdAt,
                deletedAt: $this->deletedAt
            );
        }
    }
}