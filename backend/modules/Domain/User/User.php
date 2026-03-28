<?php

namespace Modules\Domain\User;

use DateTimeImmutable;
use Modules\Domain\ValueObjects\Email;
use Modules\Domain\ValueObjects\HashedPassword;
use Modules\Domain\ValueObjects\UserFullName;
use Modules\Domain\ValueObjects\Username;

final class User
{
    private function __construct(
        private readonly string $id,
        private UserFullName $firstName,
        private UserFullName $lastName,
        private Email $email,
        private HashedPassword $password,
        private Username $username,
        private ?string $picture,
        private ?string $bio,
        private ?DateTimeImmutable $emailVerifiedAt,
        private readonly DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
        private ?DateTimeImmutable $deletedAt,
    ) {}

    public static function create(
        string $id,
        string $firstName,
        string $lastName,
        string $email,
        HashedPassword $password,
        string $username,
        ?string $picture = null,
        ?string $bio = null,
        ?DateTimeImmutable $emailVerifiedAt = null,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
        ?DateTimeImmutable $deletedAt = null,
    ): self {
        $now = new DateTimeImmutable;

        return new self(
            id: $id,
            firstName: new UserFullName($firstName),
            lastName: new UserFullName($lastName),
            email: new Email($email),
            password: $password,
            username: new Username($username),
            picture: $picture,
            bio: $bio,
            emailVerifiedAt: $emailVerifiedAt,
            createdAt: $createdAt ?? $now,
            updatedAt: $updatedAt ?? $now,
            deletedAt: $deletedAt,
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getFirstName(): UserFullName
    {
        return $this->firstName;
    }

    public function getLastName(): UserFullName
    {
        return $this->lastName;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPassword(): HashedPassword
    {
        return $this->password;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getEmailVerifiedAt(): ?DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    public function isEmailVerified(): bool
    {
        return $this->emailVerifiedAt !== null;
    }

    public function markEmailAsVerified(): void
    {
        $this->emailVerifiedAt = new DateTimeImmutable;
    }

    public function updateProfileDetails(
        string $firstName,
        string $lastName,
        string $username,
        ?string $bio,
    ): void {
        $this->firstName = new UserFullName($firstName);
        $this->lastName = new UserFullName($lastName);
        $this->username = new Username($username);
        $this->bio = $bio;
    }
}
