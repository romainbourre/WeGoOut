<?php

namespace Adapters\InMemory\Repositories;

use Adapters\InMemory\Entities\User as DatabaseUser;
use Business\Entities\User;
use Business\Exceptions\ValidationException;
use Business\Ports\UserRepositoryInterface;
use Business\ValueObjects\Email;
use PhpLinq\Exceptions\InvalidQueryResultException;
use PhpLinq\Interfaces\ILinq;
use PhpLinq\PhpLinq;
use Tests\Utils\Builders\UserBuilder;

class InMemoryUserRepository implements UserRepositoryInterface
{
    private int $currentId = 0;

    public readonly ILinq $users;

    public function __construct()
    {
        $this->users = new PhpLinq();
    }

    /**
     * @throws InvalidQueryResultException
     * @throws ValidationException
     */
    public function setPassword(string $userId, string $newPassword): void
    {
        /** @var DatabaseUser $savedUser */
        $savedUser = $this->users->first(fn(DatabaseUser $u) => $u->id == $userId);
        $this->users->remove($savedUser);
        $updatedUser = DatabaseUser::from($savedUser->toDomainUser(), $newPassword);
        $this->users->add($updatedUser);
    }

    /**
     * @throws InvalidQueryResultException
     * @throws ValidationException
     */
    public function setAccountAsValid(int $userId): void
    {
        /** @var DatabaseUser $savedUser */
        $savedUser = $this->users->first(fn(DatabaseUser $user) => $user->id == $userId);
        $userToUpdate = new User(
            id: $savedUser->id,
            email: $savedUser->email,
            firstname: $savedUser->firstname,
            lastname: $savedUser->lastname,
            picture: $savedUser->picture,
            description: $savedUser->description,
            birthDate: $savedUser->birthDate,
            location: $savedUser->location,
            validationToken: null,
            genre: $savedUser->genre,
            createdAt: $savedUser->createdAt,
            deletedAt: $savedUser->deletedAt
        );
        $this->update($userToUpdate);
    }

    public function setValidationToken(int $userId, string $token): void
    {
        // TODO: Implement setValidationToken() method.
    }

    public function getUserByEmail(Email $email): User|null
    {
        return $this->users->firstOrNull(fn(User $user) => $user->email == $email);
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
     * @throws InvalidQueryResultException
     * @throws ValidationException
     */
    private function update(User $user): void
    {
        /** @var DatabaseUser $savedUser */
        $savedUser = $this->users->first(fn(DatabaseUser $u) => $u->id == $user->id);
        $this->users->remove($savedUser);
        $this->users->add(DatabaseUser::from($user, $savedUser->password));
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

    /**
     * @throws ValidationException
     */
    public function haveAlreadyUser(): User
    {
        $user = UserBuilder::given()->create();
        return $this->addUserWithPassword($user, 'password');
    }

    public function setNextId(int $nextId): void
    {
        $this->currentId = $nextId;
    }
}
