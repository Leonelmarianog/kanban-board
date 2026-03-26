<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Application\UseCases\Auth\SendVerificationEmail\SendVerificationEmailRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class SendVerificationEmailRepository implements SendVerificationEmailRepositoryInterface
{
    private const TOKEN_LENGTH = 64;

    public function __construct(
        private UserModel $userModel,
        private EmailVerificationTokenModel $tokenModel,
    ) {}

    public function findByEmail(string $email): ?User
    {
        $model = $this->userModel->where('email', $email)->first();

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function createVerificationToken(User $user): string
    {
        return DB::transaction(function () use ($user): string {
            // Invalidate any existing unused tokens for this user
            $this->tokenModel
                ->where('user_id', $user->getId())
                ->whereNull('used_at')
                ->delete();

            // Create a new token
            $token = Str::random(self::TOKEN_LENGTH);
            $expiresAt = now()->addSeconds(config('verification.token_expiration_seconds', 900));

            $this->tokenModel->create([
                'user_id' => $user->getId(),
                'token' => $token,
                'expires_at' => $expiresAt,
            ]);

            return $token;
        });
    }
}
