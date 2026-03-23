<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Member\UpdateProfileDetails\UpdateProfileDetailsRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class UpdateProfileDetailsRepository implements UpdateProfileDetailsRepositoryInterface
{
    public function __construct(
        private UserModel $model,
    ) {}

    public function findById(string $id): ?User
    {
        $model = $this->model->find($id);

        if (! $model) {
            return null;
        }

        return UserMapper::toDomain($model);
    }

    public function update(User $user): User
    {
        $model = $this->model->findOrFail($user->getId());

        $model->fill([
            'first_name' => $user->getFirstName()->getValue(),
            'last_name' => $user->getLastName()->getValue(),
            'username' => $user->getUsername()->getValue(),
            'bio' => $user->getBio(),
        ]);

        $model->save();

        return UserMapper::toDomain($model);
    }

    public function usernameExists(string $username, string $excludeUserId): bool
    {
        return $this->model->where('username', $username)
            ->where('id', '!=', $excludeUserId)
            ->exists();
    }
}
