<?php

namespace Modules\Infrastructure\Persistence\Mappers;

use DateTimeImmutable;
use Modules\Domain\User\User;
use Modules\Domain\ValueObjects\HashedPassword;
use Modules\Infrastructure\Persistence\Models\UserModel;

final class UserMapper
{
    public static function toDomain(UserModel $model): User
    {
        return User::create(
            id: $model->id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            email: $model->email,
            password: HashedPassword::fromHash($model->password),
            username: $model->username,
            picture: $model->picture,
            bio: $model->bio,
            createdAt: new DateTimeImmutable($model->created_at->toIso8601String()),
            updatedAt: new DateTimeImmutable($model->updated_at->toIso8601String()),
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toIso8601String()) : null,
        );
    }

    public static function toModel(User $user): UserModel
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

        return $model;
    }
}
