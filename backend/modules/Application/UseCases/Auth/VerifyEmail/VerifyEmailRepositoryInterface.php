<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

use Modules\Domain\Auth\EmailVerificationToken;
use Modules\Domain\User\User;

interface VerifyEmailRepositoryInterface
{
    /**
     * Find a valid (unused, non-expired) verification token.
     */
    public function findValidToken(string $token): ?EmailVerificationToken;

    /**
     * Find a user by their ID.
     */
    public function findByUserId(string $userId): ?User;

    /**
     * Mark the token as used by its ID.
     */
    public function markTokenAsUsed(string $tokenId): void;

    /**
     * Mark the user's email as verified.
     */
    public function markEmailAsVerified(User $user): void;
}
