<?php

namespace Modules\Application\UseCases\Auth\ChangePassword;

final readonly class ChangePasswordResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
