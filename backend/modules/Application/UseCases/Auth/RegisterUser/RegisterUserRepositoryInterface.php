<?php

namespace Modules\Application\UseCases\Auth\RegisterUser;

use Modules\Domain\User\User;

interface RegisterUserRepositoryInterface
{
    public function emailExists(string $email): bool;

    public function usernameExists(string $username): bool;

    public function save(User $user): User;

    public function createVerificationToken(User $user): string;
}
