<?php

namespace Modules\Domain\ValueObjects\Exceptions;

use Modules\Domain\Exceptions\ValidationDomainException;

final class InvalidFullNameLengthException extends ValidationDomainException
{
    public function __construct()
    {
        parent::__construct('Name must be between 1 and 255 characters.');
    }
}
