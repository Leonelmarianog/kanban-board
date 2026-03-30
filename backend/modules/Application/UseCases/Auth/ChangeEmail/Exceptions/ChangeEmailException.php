<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

class ChangeEmailException extends ApplicationException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
