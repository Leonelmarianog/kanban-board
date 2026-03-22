<?php

namespace Modules\Domain\ValueObjects;

use Modules\Domain\ValueObjects\Exceptions\InvalidEmailException;

final readonly class Email
{
    /**
     * @throws InvalidEmailException
     */
    public function __construct(
        private string $value
    ) {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($value);
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Email $other): bool
    {
        return $this->value === $other->value;
    }
}
