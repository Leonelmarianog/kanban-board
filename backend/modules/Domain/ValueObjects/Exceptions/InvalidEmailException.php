<?php

namespace Modules\Domain\ValueObjects\Exceptions;

use Modules\Domain\Exceptions\ValidationDomainException;

final class InvalidEmailException extends ValidationDomainException
{
    public function __construct(string $email)
    {
        parent::__construct("Invalid email format: {$email}");
    }
}
