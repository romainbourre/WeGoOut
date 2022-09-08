<?php


namespace Domain\Interfaces;


use Domain\Entities\User;

interface IUserRepository
{
    public function setPassword(string $email, string $pwd): bool;

    public function setAccountAsValid(int $userId): void;

    public function setValidationToken(int $userId, string $token): void;

    public function getUserByEmailAndPassword(string $email, string $password);

    public function addUserWithPassword(User $user, string $password);

    public function isEmailExist(string $email): bool;
}
