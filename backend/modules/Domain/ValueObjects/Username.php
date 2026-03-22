<?php

namespace Modules\Domain\ValueObjects;

use Modules\Domain\ValueObjects\Exceptions\InvalidUsernameFormatException;
use Modules\Domain\ValueObjects\Exceptions\InvalidUsernameLengthException;

final readonly class Username
{
    /**
     * @throws InvalidUsernameLengthException
     * @throws InvalidUsernameFormatException
     */
    public function __construct(
        private string $value
    ) {
        if (strlen($value) < 3 || strlen($value) > 50) {
            throw new InvalidUsernameLengthException;
        }

        if (! preg_match('/^[a-zA-Z0-9_]+$/', $value)) {
            throw new InvalidUsernameFormatException;
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(Username $other): bool
    {
        return $this->value === $other->value;
    }
}
