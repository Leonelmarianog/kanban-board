<?php

namespace Modules\Application\UseCases\Auth\CancelEmailChange;

final readonly class CancelEmailChangeRequestDto
{
    public function __construct(
        public string $token,
        public int $expires,
        public string $signature,
    ) {}
}
