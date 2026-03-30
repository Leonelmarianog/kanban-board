<?php

namespace Modules\Application\UseCases\Auth\ConfirmEmailChange;

final readonly class ConfirmEmailChangeRequestDto
{
    public function __construct(
        public string $token,
        public int $expires,
        public string $signature,
    ) {}
}
