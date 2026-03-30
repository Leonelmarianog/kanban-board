<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class EmailAlreadyInUseException extends ApplicationException
{
    public function __construct(string $email)
    {
        parent::__construct("The email '{$email}' is already in use by another account.");
    }
}
