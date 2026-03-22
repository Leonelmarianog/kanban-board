<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Member\GetMember\GetMemberRepositoryInterface;
use Modules\Domain\User\User;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

final readonly class GetMemberRepository implements GetMemberRepositoryInterface
{
    public function findById(string $id): ?User
    {
        $model = UserModel::find($id);

        if ($model === null) {
            return null;
        }

        return UserMapper::toDomain($model);
    }
}
