<?php

namespace Modules\Application\UseCases\Auth\ChangePassword;

final readonly class ChangePasswordRequestDto
{
    public function __construct(
        public string $userId,
        public string $currentPassword,
        public string $newPassword,
        public string $currentTokenId,
    ) {}
}
