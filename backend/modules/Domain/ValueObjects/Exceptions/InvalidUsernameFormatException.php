<?php

namespace Modules\Domain\ValueObjects\Exceptions;

use Modules\Domain\Exceptions\ValidationDomainException;

final class InvalidUsernameFormatException extends ValidationDomainException
{
    public function __construct()
    {
        parent::__construct('Username can only contain letters, numbers, and underscores.');
    }
}
