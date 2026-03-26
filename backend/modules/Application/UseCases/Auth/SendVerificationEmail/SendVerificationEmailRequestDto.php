<?php

namespace Modules\Application\UseCases\Auth\SendVerificationEmail;

final readonly class SendVerificationEmailRequestDto
{
    public function __construct(
        public string $email,
    ) {}
}
