<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

use Modules\Application\UseCases\Auth\VerifyEmail\Exceptions\InvalidVerificationLinkException;
use Modules\Domain\User\User;

interface VerifyEmailRepositoryInterface
{
    /**
     * @throws InvalidVerificationLinkException
     */
    public function findById(string $id): User;

    public function findValidToken(string $token): ?VerificationTokenDto;

    public function markTokenAsUsed(string $tokenId): void;

    public function markEmailAsVerified(User $user): void;
}
