<?php

namespace Modules\Application\UseCases\Auth\LoginUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class LoginUserException extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
