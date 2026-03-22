<?php

namespace Modules\Domain\ValueObjects;

use Modules\Domain\ValueObjects\Exceptions\InvalidFullNameLengthException;

final readonly class UserFullName
{
    /**
     * @throws InvalidFullNameLengthException
     */
    public function __construct(
        private string $value
    ) {
        if (strlen($value) < 1 || strlen($value) > 255) {
            throw new InvalidFullNameLengthException;
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(UserFullName $other): bool
    {
        return $this->value === $other->value;
    }
}
