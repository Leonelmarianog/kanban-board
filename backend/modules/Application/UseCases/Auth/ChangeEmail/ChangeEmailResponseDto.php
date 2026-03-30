<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail;

final readonly class ChangeEmailResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
