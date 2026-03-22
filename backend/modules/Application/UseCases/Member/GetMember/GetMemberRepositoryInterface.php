<?php

namespace Modules\Application\UseCases\Member\GetMember;

use Modules\Domain\User\User;

interface GetMemberRepositoryInterface
{
    public function findById(string $id): ?User;
}
