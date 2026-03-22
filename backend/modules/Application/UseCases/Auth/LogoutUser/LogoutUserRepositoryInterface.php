<?php

namespace Modules\Application\UseCases\Auth\LogoutUser;

interface LogoutUserRepositoryInterface
{
    public function revokeCurrentToken(string $userId, int $tokenId): void;
}
