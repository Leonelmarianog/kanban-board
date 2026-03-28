<?php

namespace Modules\Infrastructure\SignedUrl;

interface SignedUrlInterface
{
    /**
     * Sign a URL with an expiration.
     */
    public function sign(string $url, int $expirationSeconds): string;

    /**
     * Validate a signed URL.
     */
    public function validate(string $fullUrl): bool;
}
