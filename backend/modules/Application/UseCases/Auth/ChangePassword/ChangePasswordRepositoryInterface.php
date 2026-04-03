<?php

namespace Modules\Application\UseCases\Auth\ChangePassword;

use Modules\Domain\User\User;

interface ChangePasswordRepositoryInterface
{
    public function findById(string $userId): ?User;

    public function updatePassword(User $user): void;

    public function revokeOtherTokens(string $userId, string $currentTokenId): void;
}
