<?php

namespace Modules\Application\UseCases\Auth\LoginUser;

use Modules\Domain\User\User;

interface LoginUserRepositoryInterface
{
    public function findByEmailOrUsername(string $emailOrUsername): ?User;

    public function createToken(User $user, string $tokenName): ?string;
}
