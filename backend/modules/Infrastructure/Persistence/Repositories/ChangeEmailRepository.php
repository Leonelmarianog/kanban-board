<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\ChangeEmail\ChangeEmailRepositoryInterface;
use Modules\Domain\Auth\EmailChangeToken;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\EmailChangeTokenMapper;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class ChangeEmailRepository implements ChangeEmailRepositoryInterface
{
    public function __construct(
        private UserModel $userModel,
        private EmailChangeTokenModel $tokenModel,
    ) {}

    public function findById(string $id): ?User
    {
        $model = $this->userModel->find($id);

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function findPendingByUserId(string $userId): ?EmailChangeToken
    {
        $model = $this->tokenModel
            ->where('user_id', $userId)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($model === null) {
            return null;
        }

        return EmailChangeTokenMapper::toDomain($model);
    }

    public function emailExists(string $email, string $excludeUserId): bool
    {
        return $this->userModel
            ->where('email', $email)
            ->where('id', '!=', $excludeUserId)
            ->exists();
    }

    public function deleteToken(string $tokenId): void
    {
        $this->tokenModel->where('id', $tokenId)->delete();
    }

    public function saveToken(EmailChangeToken $token): void
    {
        $this->tokenModel->create([
            'id' => $token->getId(),
            'user_id' => $token->getUserId(),
            'current_email' => $token->getCurrentEmail(),
            'new_email' => $token->getNewEmail(),
            'token' => $token->getToken(),
            'expires_at' => $token->getExpiresAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
