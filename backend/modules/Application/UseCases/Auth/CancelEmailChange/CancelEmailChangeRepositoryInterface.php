<?php

namespace Modules\Application\UseCases\Auth\CancelEmailChange;

use Modules\Domain\Auth\EmailChangeToken;

interface CancelEmailChangeRepositoryInterface
{
    /**
     * Find a valid (unconfirmed, non-expired) email change token by its token value.
     */
    public function findValidToken(string $token): ?EmailChangeToken;

    /**
     * Delete an email change token by its ID.
     */
    public function deleteToken(string $tokenId): void;
}
