<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

final readonly class VerifyEmailRequestDto
{
    public function __construct(
        public string $token,
        public int $expires,
        public string $signature,
    ) {}
}
