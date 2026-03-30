<?php

namespace Modules\Application\UseCases\Auth\CancelEmailChange;

use Modules\Application\UseCases\Auth\CancelEmailChange\Exceptions\InvalidEmailChangeTokenException;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;

final readonly class CancelEmailChangeHandler
{
    public function __construct(
        private CancelEmailChangeRepositoryInterface $repository,
        private SignedUrlInterface $urlSigner,
    ) {}

    public function execute(CancelEmailChangeRequestDto $request): CancelEmailChangeResponseDto
    {
        // 1. Validate the signed URL
        $frontendUrl = $this->buildFrontendCancelUrl(
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

        // 3. Delete the token
        $this->repository->deleteToken($token->getId());

        return new CancelEmailChangeResponseDto(
            message: 'Email change request cancelled successfully.',
        );
    }

    private function buildFrontendCancelUrl(string $token, int $expires, string $signature): string
    {
        $baseUrl = config('app.frontend_url').'/email-change/cancel';

        return $baseUrl.'?token='.$token.'&expires='.$expires.'&signature='.$signature;
    }
}
