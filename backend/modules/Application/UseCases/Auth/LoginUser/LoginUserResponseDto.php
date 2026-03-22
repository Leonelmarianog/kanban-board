<?php

namespace Modules\Application\UseCases\Auth\LoginUser;

final readonly class LoginUserResponseDto
{
    public function __construct(
        public string $token,
    ) {}
}
