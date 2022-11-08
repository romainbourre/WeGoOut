<?php


namespace Business\Ports;


use Business\Entities\User;
use Business\ValueObjects\Email;

interface UserRepositoryInterface
{
    public function setPassword(string $userId, string $newPassword): void;

    public function setAccountAsValid(int $userId): void;

    public function setValidationToken(int $userId, string $token): void;

    public function getUserByEmail(Email $email): User|null;

    public function getUserByEmailAndPassword(string $email, string $password): User|null;

    public function addUserWithPassword(User $user, string $password);

    public function isEmailExist(string $email): bool;
}
