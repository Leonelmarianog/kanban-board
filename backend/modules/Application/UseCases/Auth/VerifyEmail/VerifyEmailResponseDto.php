<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

final readonly class VerifyEmailResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
