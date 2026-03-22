<?php

namespace Modules\Application\UseCases\Auth\LoginUser\Exceptions;

final class InvalidCredentialsException extends LoginUserException
{
    public function __construct(
        string $message = 'Invalid credentials.',
        int $statusCode = 401
    ) {
        parent::__construct($message, $statusCode);
    }
}
