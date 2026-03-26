<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Illuminate\Support\Str;
use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class RegisterUserRepository implements RegisterUserRepositoryInterface
{
    private const TOKEN_LENGTH = 64;

    public function __construct(
        private UserModel $model,
        private EmailVerificationTokenModel $tokenModel,
    ) {}

    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }

    public function usernameExists(string $username): bool
    {
        return $this->model->where('username', $username)->exists();
    }

    public function save(User $user): User
    {
        $model = new UserModel;
        $model->id = $user->getId();
        $model->first_name = $user->getFirstName()->getValue();
        $model->last_name = $user->getLastName()->getValue();
        $model->email = $user->getEmail()->getValue();
        $model->password = $user->getPassword()->getHashedValue();
        $model->username = $user->getUsername()->getValue();
        $model->picture = $user->getPicture();
        $model->bio = $user->getBio();
        $model->save();

        return UserMapper::toDomain($model->fresh());
    }

    public function createVerificationToken(User $user): string
    {
        $token = Str::random(self::TOKEN_LENGTH);
        $expiresAt = now()->addSeconds(config('verification.token_expiration_seconds', 900));

        $this->tokenModel->create([
            'user_id' => $user->getId(),
            'token' => $token,
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }
}
