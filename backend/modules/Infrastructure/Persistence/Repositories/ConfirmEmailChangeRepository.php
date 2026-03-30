<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Laravel\Sanctum\PersonalAccessToken;
use Modules\Application\UseCases\Auth\ConfirmEmailChange\ConfirmEmailChangeRepositoryInterface;
use Modules\Domain\Auth\EmailChangeToken;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\EmailChangeTokenMapper;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class ConfirmEmailChangeRepository implements ConfirmEmailChangeRepositoryInterface
{
    public function __construct(
        private UserModel $userModel,
        private EmailChangeTokenModel $tokenModel,
    ) {}

    public function findValidToken(string $token): ?EmailChangeToken
    {
        $model = $this->tokenModel
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('confirmed_at')
            ->first();

        if ($model === null) {
            return null;
        }

        return EmailChangeTokenMapper::toDomain($model);
    }

    public function findByUserId(string $userId): ?User
    {
        $model = $this->userModel->find($userId);

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function markTokenAsConfirmed(string $tokenId): void
    {
        $this->tokenModel->where('id', $tokenId)->update([
            'confirmed_at' => now(),
        ]);
    }

    public function updateEmail(User $user, string $newEmail): void
    {
        $this->userModel->where('id', $user->getId())->update([
            'email' => $newEmail,
            'email_verified_at' => now(),
        ]);
    }

    public function revokeAllTokens(string $userId): void
    {
        PersonalAccessToken::where('tokenable_id', $userId)->delete();
    }
}
