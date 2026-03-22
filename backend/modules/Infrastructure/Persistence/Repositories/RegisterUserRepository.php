<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\RegisterUser\RegisterUserRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class RegisterUserRepository implements RegisterUserRepositoryInterface
{
    public function __construct(
        private UserModel $model,
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
        $model = UserMapper::toModel($user);
        $model->save();

        return UserMapper::toDomain($model->fresh());
    }

    public function createToken(User $user, string $tokenName): ?string
    {
        $model = $this->model->find($user->getId());

        return $model?->createToken($tokenName)->plainTextToken;
    }
}
