<?php

namespace Modules\Domain\Auth;

use DateTimeImmutable;

final class EmailChangeToken
{
    private function __construct(
        private string $id,
        private string $userId,
        private string $currentEmail,
        private string $newEmail,
        private string $token,
        private DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $confirmedAt = null,
    ) {}

    public static function create(
        string $id,
        string $userId,
        string $currentEmail,
        string $newEmail,
        string $token,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $confirmedAt = null,
    ): self {
        return new self(
            id: $id,
            userId: $userId,
            currentEmail: $currentEmail,
            newEmail: $newEmail,
            token: $token,
            expiresAt: $expiresAt,
            confirmedAt: $confirmedAt,
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

    public function getCurrentEmail(): string
    {
        return $this->currentEmail;
    }

    public function getNewEmail(): string
    {
        return $this->newEmail;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getConfirmedAt(): ?DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmedAt !== null;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable;
    }

    public function isValid(): bool
    {
        return ! $this->isConfirmed() && ! $this->isExpired();
    }
}
