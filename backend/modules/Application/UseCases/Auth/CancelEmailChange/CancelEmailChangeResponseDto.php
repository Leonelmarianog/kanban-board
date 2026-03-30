<?php

namespace Modules\Application\UseCases\Auth\CancelEmailChange;

final readonly class CancelEmailChangeResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
