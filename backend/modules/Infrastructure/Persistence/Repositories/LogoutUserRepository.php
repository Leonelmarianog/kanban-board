<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\LogoutUser\LogoutUserRepositoryInterface;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class LogoutUserRepository implements LogoutUserRepositoryInterface
{
    public function __construct(
        private UserModel $model,
    ) {}

    public function revokeCurrentToken(string $userId, int $tokenId): void
    {
        $user = $this->model->find($userId);

        if ($user === null) {
            return;
        }

        $user->tokens()->where('id', $tokenId)->delete();
    }
}
