<?php

namespace Modules\Application\UseCases\Auth\LoginUser;

final readonly class LoginUserRequestDto
{
    public function __construct(
        public string $emailOrUsername,
        public string $password,
    ) {}
}
