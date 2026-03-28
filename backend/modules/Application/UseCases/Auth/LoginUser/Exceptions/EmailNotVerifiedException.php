<?php

namespace Modules\Application\UseCases\Auth\LoginUser\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class EmailNotVerifiedException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Please verify your email address before logging in.',
        );
    }
}
