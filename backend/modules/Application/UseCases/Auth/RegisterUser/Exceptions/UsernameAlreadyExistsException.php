<?php

namespace Modules\Application\UseCases\Auth\RegisterUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class UsernameAlreadyExistsException extends ApplicationException
{
    public function __construct(string $username)
    {
        parent::__construct("Username '{$username}' is already taken.");
    }
}
