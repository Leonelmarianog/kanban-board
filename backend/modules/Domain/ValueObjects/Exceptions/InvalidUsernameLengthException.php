<?php

namespace Modules\Domain\ValueObjects\Exceptions;

use Modules\Domain\Exceptions\ValidationDomainException;

final class InvalidUsernameLengthException extends ValidationDomainException
{
    public function __construct()
    {
        parent::__construct('Username must be between 3 and 50 characters.');
    }
}
