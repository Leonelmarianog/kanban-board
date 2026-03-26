<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\VerifyEmail\Exceptions\InvalidVerificationLinkException;
use Modules\Application\UseCases\Auth\VerifyEmail\VerificationTokenDto;
use Modules\Application\UseCases\Auth\VerifyEmail\VerifyEmailRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class VerifyEmailRepository implements VerifyEmailRepositoryInterface
{
    public function __construct(
        private UserModel $userModel,
        private EmailVerificationTokenModel $tokenModel,
    ) {}

    public function findById(string $id): User
    {
        $model = $this->userModel->find($id);

        if ($model === null) {
            throw new InvalidVerificationLinkException;
        }

        return UserMapper::toDomain($model);
    }

    public function findValidToken(string $token): ?VerificationTokenDto
    {
        $model = $this->tokenModel
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if ($model === null) {
            return null;
        }

        return new VerificationTokenDto(
            id: $model->id,
            userId: $model->user_id,
            token: $model->token,
            expiresAt: $model->expires_at,
            usedAt: $model->used_at,
        );
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
