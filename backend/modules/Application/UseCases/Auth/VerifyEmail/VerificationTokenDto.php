<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

use DateTimeInterface;

final readonly class VerificationTokenDto
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $token,
        public DateTimeInterface $expiresAt,
        public ?DateTimeInterface $usedAt,
    ) {}
}
