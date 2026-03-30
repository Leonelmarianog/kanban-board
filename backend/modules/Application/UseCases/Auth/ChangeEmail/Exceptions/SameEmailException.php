<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class SameEmailException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('The new email must be different from your current email.');
    }
}
