<?php

namespace Modules\Application\UseCases\Auth\VerifyEmail;

use Modules\Application\UseCases\Auth\VerifyEmail\Exceptions\InvalidVerificationLinkException;
use Modules\Infrastructure\Persistence\TransactionInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;

final readonly class VerifyEmailHandler
{
    public function __construct(
        private VerifyEmailRepositoryInterface $repository,
        private TransactionInterface $transaction,
        private SignedUrlInterface $urlSigner,
    ) {}

    public function execute(VerifyEmailRequestDto $request): VerifyEmailResponseDto
    {
        // 1. Validate the signed URL
        $frontendUrl = $this->buildFrontendVerificationUrl(
            token: $request->token,
            expires: $request->expires,
            signature: $request->signature,
        );

        if (! $this->urlSigner->validate($frontendUrl)) {
            throw new InvalidVerificationLinkException;
        }

        // 2. Find valid token (returns null if invalid/expired/used)
        $token = $this->repository->findValidToken($request->token);

        if ($token === null) {
            throw new InvalidVerificationLinkException;
        }

        // 3. Find user (returns null if not found)
        $user = $this->repository->findByUserId($token->getUserId());

        if ($user === null) {
            throw new InvalidVerificationLinkException;
        }

        // 4. Check if already verified
        if ($user->isEmailVerified()) {
            return new VerifyEmailResponseDto(
                message: 'Email already verified.',
            );
        }

        // 5. Mark token as used and verify email
        $this->transaction->execute(function () use ($token, $user): void {
            $this->repository->markTokenAsUsed($token->getId());
            $this->repository->markEmailAsVerified($user);
        });

        return new VerifyEmailResponseDto(
            message: 'Email verified successfully.',
        );
    }

    private function buildFrontendVerificationUrl(string $token, int $expires, string $signature): string
    {
        $baseUrl = config('app.frontend_url').'/verify-email';

        return $baseUrl.'?token='.$token.'&expires='.$expires.'&signature='.$signature;
    }
}
