<?php

namespace Modules\Application\UseCases\Auth\LoginUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class LoginUserException extends ApplicationException
{
    public function __construct(string $message, int $statusCode = 500)
    {
        parent::__construct($message, $statusCode);
    }
}
