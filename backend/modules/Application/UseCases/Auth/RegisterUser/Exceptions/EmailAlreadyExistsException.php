<?php

namespace Modules\Application\UseCases\Auth\RegisterUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class EmailAlreadyExistsException extends ApplicationException
{
    public function __construct(string $email)
    {
        parent::__construct("Email '{$email}' is already registered.");
    }
}
