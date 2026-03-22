<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\LoginUser\LoginUserRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class LoginUserRepository implements LoginUserRepositoryInterface
{
    public function __construct(
        private UserModel $model,
    ) {}

    public function findByEmailOrUsername(string $emailOrUsername): ?User
    {
        $model = $this->model
            ->where('email', $emailOrUsername)
            ->orWhere('username', $emailOrUsername)
            ->first();

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function createToken(User $user, string $tokenName): ?string
    {
        $model = $this->model->find($user->getId());

        return $model?->createToken($tokenName)->plainTextToken;
    }
}
