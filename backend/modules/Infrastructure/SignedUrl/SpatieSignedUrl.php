<?php

namespace Modules\Infrastructure\SignedUrl;

use Spatie\UrlSigner\Sha256UrlSigner;

final readonly class SpatieSignedUrl implements SignedUrlInterface
{
    public function __construct(
        private Sha256UrlSigner $urlSigner,
    ) {}

    public function sign(string $url, int $expirationSeconds): string
    {
        $expiration = now()->addSeconds($expirationSeconds);

        return $this->urlSigner->sign($url, $expiration);
    }

    public function validate(string $fullUrl): bool
    {
        return $this->urlSigner->validate($fullUrl);
    }
}
