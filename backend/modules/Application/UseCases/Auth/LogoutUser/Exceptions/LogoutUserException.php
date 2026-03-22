<?php

namespace Modules\Application\UseCases\Auth\LogoutUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class LogoutUserException extends ApplicationException
{
    public function __construct(string $message = 'Failed to logout.', int $statusCode = 500)
    {
        parent::__construct($message, $statusCode);
    }
}
