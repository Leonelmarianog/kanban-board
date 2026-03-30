<?php

namespace Modules\Application\UseCases\Auth\CancelEmailChange\Exceptions;

use Modules\Application\Exceptions\ApplicationException;

final class InvalidEmailChangeTokenException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Invalid or expired cancellation link.',
        );
    }
}
