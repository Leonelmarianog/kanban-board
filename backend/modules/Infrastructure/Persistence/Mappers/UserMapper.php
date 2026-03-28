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
            emailVerifiedAt: $model->email_verified_at
                ? new DateTimeImmutable($model->email_verified_at->toIso8601String())
                : null,
            createdAt: new DateTimeImmutable($model->created_at->toIso8601String()),
            updatedAt: new DateTimeImmutable($model->updated_at->toIso8601String()),
            deletedAt: $model->deleted_at ? new DateTimeImmutable($model->deleted_at->toIso8601String()) : null,
        );
    }
}
