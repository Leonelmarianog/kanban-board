<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Laravel\Sanctum\PersonalAccessToken;
use Modules\Application\UseCases\Auth\ChangePassword\ChangePasswordRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class ChangePasswordRepository implements ChangePasswordRepositoryInterface
{
    public function __construct(
        private UserModel $model,
    ) {}

    public function findById(string $userId): ?User
    {
        $model = $this->model->find($userId);

        return $model ? UserMapper::toDomain($model) : null;
    }

    public function updatePassword(User $user): void
    {
        $model = $this->model->findOrFail($user->getId());

        $model->fill([
            'password' => $user->getPassword()->getHashedValue(),
        ]);

        $model->save();
    }

    public function revokeOtherTokens(string $userId, string $currentTokenId): void
    {
        PersonalAccessToken::where('tokenable_id', $userId)
            ->where('id', '!=', $currentTokenId)
            ->delete();
    }
}
