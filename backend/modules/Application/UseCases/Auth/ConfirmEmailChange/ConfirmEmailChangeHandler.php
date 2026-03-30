<?php

namespace Modules\Application\UseCases\Auth\ConfirmEmailChange;

use Modules\Application\UseCases\Auth\ConfirmEmailChange\Exceptions\InvalidEmailChangeTokenException;
use Modules\Infrastructure\Persistence\TransactionInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;

final readonly class ConfirmEmailChangeHandler
{
    public function __construct(
        private ConfirmEmailChangeRepositoryInterface $repository,
        private TransactionInterface $transaction,
        private SignedUrlInterface $urlSigner,
    ) {}

    public function execute(ConfirmEmailChangeRequestDto $request): ConfirmEmailChangeResponseDto
    {
        // 1. Validate the signed URL
        $frontendUrl = $this->buildFrontendConfirmationUrl(
            token: $request->token,
            expires: $request->expires,
            signature: $request->signature,
        );

        if (! $this->urlSigner->validate($frontendUrl)) {
            throw new InvalidEmailChangeTokenException;
        }

        // 2. Find valid token (returns null if invalid/expired/confirmed)
        $token = $this->repository->findValidToken($request->token);

        if ($token === null) {
            throw new InvalidEmailChangeTokenException;
        }

        // 3. Find user (returns null if not found)
        $user = $this->repository->findByUserId($token->getUserId());

        if ($user === null) {
            throw new InvalidEmailChangeTokenException;
        }

        // 4. Mark token as confirmed, update email, and revoke tokens
        $this->transaction->execute(function () use ($token, $user): void {
            $this->repository->markTokenAsConfirmed($token->getId());
            $this->repository->updateEmail($user, $token->getNewEmail());
            $this->repository->revokeAllTokens($user->getId());
        });

        return new ConfirmEmailChangeResponseDto(
            message: 'Email changed successfully. Please log in with your new email.',
        );
    }

    private function buildFrontendConfirmationUrl(string $token, int $expires, string $signature): string
    {
        $baseUrl = config('app.frontend_url').'/email-change/confirm';

        return $baseUrl.'?token='.$token.'&expires='.$expires.'&signature='.$signature;
    }
}
