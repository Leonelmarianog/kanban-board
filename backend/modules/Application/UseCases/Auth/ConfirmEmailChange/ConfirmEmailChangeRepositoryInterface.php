<?php

namespace Modules\Application\UseCases\Auth\ConfirmEmailChange;

use Modules\Domain\Auth\EmailChangeToken;
use Modules\Domain\User\User;

interface ConfirmEmailChangeRepositoryInterface
{
    /**
     * Find a valid (unconfirmed, non-expired) email change token.
     */
    public function findValidToken(string $token): ?EmailChangeToken;

    /**
     * Find a user by their ID.
     */
    public function findByUserId(string $userId): ?User;

    /**
     * Mark the token as confirmed by its ID.
     */
    public function markTokenAsConfirmed(string $tokenId): void;

    /**
     * Update the user's email address.
     */
    public function updateEmail(User $user, string $newEmail): void;

    /**
     * Revoke all authentication tokens for the user.
     */
    public function revokeAllTokens(string $userId): void;
}
