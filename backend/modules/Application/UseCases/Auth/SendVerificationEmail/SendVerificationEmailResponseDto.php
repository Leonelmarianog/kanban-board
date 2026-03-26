<?php

namespace Modules\Application\UseCases\Auth\SendVerificationEmail;

final readonly class SendVerificationEmailResponseDto
{
    public function __construct(
        public string $message,
    ) {}
}
