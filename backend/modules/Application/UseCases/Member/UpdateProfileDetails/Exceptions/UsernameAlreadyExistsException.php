<?php

namespace Modules\Application\UseCases\Member\UpdateProfileDetails\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class UsernameAlreadyExistsException extends ApplicationException
{
    public function __construct(string $username)
    {
        parent::__construct("Username '{$username}' is already taken.");
    }
}
