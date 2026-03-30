<?php

namespace Modules\Application\UseCases\Auth\ConfirmEmailChange;

final readonly class ConfirmEmailChangeResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
