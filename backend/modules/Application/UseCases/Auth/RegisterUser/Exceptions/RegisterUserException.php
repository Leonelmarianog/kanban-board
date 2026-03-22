<?php

namespace Modules\Application\UseCases\Auth\RegisterUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class RegisterUserException extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
