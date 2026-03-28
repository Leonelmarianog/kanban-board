<?php

namespace Modules\Application\UseCases\Auth\LoginUser\Exceptions;

final class InvalidCredentialsException extends LoginUserException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Invalid credentials.',
        );
    }
}
