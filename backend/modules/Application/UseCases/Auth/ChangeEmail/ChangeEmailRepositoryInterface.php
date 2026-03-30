<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail;

use Modules\Domain\Auth\EmailChangeToken;
use Modules\Domain\User\User;

interface ChangeEmailRepositoryInterface
{
    public function findById(string $id): ?User;

    public function findPendingByUserId(string $userId): ?EmailChangeToken;

    public function emailExists(string $email, string $excludeUserId): bool;

    public function deleteToken(string $tokenId): void;

    public function saveToken(EmailChangeToken $token): void;
}
