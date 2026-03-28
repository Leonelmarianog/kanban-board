<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailRepositoryInterface;
use Modules\Domain\Auth\EmailVerificationToken;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\EmailVerificationTokenMapper;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class VerifyEmailRepository implements VerifyEmailRepositoryInterface
{
    public function __construct(
        private UserModel $userModel,
        private EmailVerificationTokenModel $tokenModel,
    ) {}

    public function findValidToken(string $token): ?EmailVerificationToken
    {
        $model = $this->tokenModel
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if ($model === null) {
            return null;
        }

        return EmailVerificationTokenMapper::toDomain($model);
    }

    public function findByUserId(string $userId): ?User
    {
        $model = $this->userModel->find($userId);

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function markTokenAsUsed(string $tokenId): void
    {
        $this->tokenModel->where('id', $tokenId)->update([
            'used_at' => now(),
        ]);
    }

    public function markEmailAsVerified(User $user): void
    {
        $this->userModel->where('id', $user->getId())->update([
            'email_verified_at' => now(),
        ]);
    }
}
