<?php

namespace Modules\Application\UseCases\Auth\RegisterUser;

final readonly class RegisterUserRequestDto
{
    public function __construct(
        public string $firstName,
        public string $lastName,
        public string $email,
        public string $password,
        public string $username,
    ) {}
}
