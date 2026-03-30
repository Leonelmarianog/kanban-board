<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail;

final readonly class ChangeEmailRequestDto
{
    public function __construct(
        public string $userId,
        public string $newEmail,
    ) {}
}
