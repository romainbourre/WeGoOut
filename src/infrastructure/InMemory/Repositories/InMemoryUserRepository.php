<?php

namespace Infrastructure\InMemory\Repositories;

use Domain\Entities\User;
use Domain\Exceptions\ValidationException;
use Domain\Interfaces\IUserRepository;
use Infrastructure\InMemory\Entities\User as DatabaseUser;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;

class InMemoryUserRepository implements IUserRepository
{
    private int $currentId = 0;

    public readonly ILinq $users;

    public function __construct()
    {
        $this->users = new PhpLinq();
    }

    public function setPassword(string $email, string $pwd): bool
    {
        // TODO: Implement setPassword() method.
    }

    public function addUser(array $data): User
    {
        // TODO: Implement addUser() method.
    }

    public function getValidationCode(int $id)
    {
        // TODO: Implement getValidationCode() method.
    }

    public function setAccountAsValid(int $userId): void
    {
        // TODO: Implement setAccountAsValid() method.
    }

    public function setValidationToken(int $userId, string $token): void
    {
        // TODO: Implement setValidationToken() method.
    }

    public function addWithPassword(User $user, string $password): User
    {
        $this->users->add(DatabaseUser::from($user, $password));
        return $user;
    }

    public function getUserByEmailAndPassword(string $email, string $password): User|null
    {
        $databaseUser = $this->users->where(function (DatabaseUser $user) use ($email, $password)
        {
            return $user->email == $email && $user->password == $password;
        })->firstOrNull();
        if ($databaseUser == null) {
            return null;
        }
        return $databaseUser->toDomainUser();
    }

    /**
     * @throws ValidationException
     */
    public function addUserWithPassword(User $user, string $password): User
    {
        $user = self::copyUserWithNewId($this->currentId++, $user);
        $this->users->add(DatabaseUser::from($user, $password));
        return $user;
    }

    public function isEmailExist(string $email): bool
    {
        return $this->users->any(fn(User $user) => $user->email == $email);
    }

    /**
     * @throws ValidationException
     */
    private static function copyUserWithNewId(int $newId, User $user): User
    {
        return new User(
            id: $newId,
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
}