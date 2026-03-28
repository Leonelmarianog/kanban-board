<?php

namespace Modules\Domain\Auth;

use DateTimeImmutable;

final class EmailVerificationToken
{
    private function __construct(
        private string $id,
        private string $userId,
        private string $token,
        private DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $usedAt = null,
    ) {}

    public static function create(
        string $id,
        string $userId,
        string $token,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $usedAt = null,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            token: $token,
            expiresAt: $expiresAt,
            usedAt: $usedAt,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getUsedAt(): ?DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function isUsed(): bool
    {
        return $this->usedAt !== null;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable;
    }

    public function isValid(): bool
    {
        return ! $this->isUsed() && ! $this->isExpired();
    }
}
