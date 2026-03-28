<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class InvalidVerificationLinkException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Invalid or expired verification link.',
        );
    }
}
