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
        // Reconstruct the frontend URL that was signed
        $frontendUrl = $this->buildFrontendVerificationUrl(
            token: $request->token,
            expires: $request->expires,
            signature: $request->signature,
        );

        if (! $this->urlSigner->validate($frontendUrl)) {
            throw new InvalidVerificationLinkException;
        }

        $token = $this->repository->findValidToken($request->token);

        if ($token === null) {
            throw new InvalidVerificationLinkException;
        }

        $user = $this->repository->findById($token->userId);

        if ($user->isEmailVerified()) {
            return new VerifyEmailResponseDto(
                message: 'Email already verified.',
            );
        }

        $this->transaction->execute(function () use ($token, $user): void {
            $this->repository->markTokenAsUsed($token->id);
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
