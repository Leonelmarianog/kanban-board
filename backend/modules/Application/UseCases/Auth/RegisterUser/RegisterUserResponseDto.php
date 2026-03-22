<?php

namespace Modules\Application\UseCases\Auth\RegisterUser;

final readonly class RegisterUserResponseDto
{
    public function __construct(
        public string $token,
    ) {}
}
