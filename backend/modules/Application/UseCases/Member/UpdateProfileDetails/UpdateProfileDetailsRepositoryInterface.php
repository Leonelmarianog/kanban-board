<?php

namespace Modules\Application\UseCases\Member\UpdateProfileDetails;

use Modules\Domain\User\User;

interface UpdateProfileDetailsRepositoryInterface
{
    public function findById(string $id): ?User;

    public function update(User $user): User;

    public function usernameExists(string $username, string $excludeUserId): bool;
}
