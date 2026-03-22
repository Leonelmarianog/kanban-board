<?php

namespace Modules\Domain\ValueObjects;

use Illuminate\Support\Facades\Hash;

final readonly class HashedPassword
{
    private function __construct(
        private string $hashedValue
    ) {}

    public static function fromPlainText(string $plainPassword): self
    {
        return new self(Hash::make($plainPassword));
    }

    public static function fromHash(string $hashedValue): self
    {
        return new self($hashedValue);
    }

    public function getHashedValue(): string
    {
        return $this->hashedValue;
    }

    public function verify(string $plainPassword): bool
    {
        return Hash::check($plainPassword, $this->hashedValue);
    }
}
