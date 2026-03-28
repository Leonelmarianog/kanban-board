<?php

use Modules\Domain\Auth\EmailVerificationToken;

describe('EmailVerificationToken Entity', function () {
    describe('create', function () {
        it('creates a token with valid data', function () {
            $expiresAt = new DateTimeImmutable('+1 hour');
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: $expiresAt,
            );

            expect($token->getId())->toBe('123e4567-e89b-12d3-a456-426614174000')
                ->and($token->getUserId())->toBe('987e6543-e21b-12d3-a456-426614174000')
                ->and($token->getToken())->toBe('verification-token-123')
                ->and($token->getExpiresAt())->toBe($expiresAt)
                ->and($token->getUsedAt())->toBeNull();
        });

        it('creates a token with used_at date', function () {
            $expiresAt = new DateTimeImmutable('+1 hour');
            $usedAt = new DateTimeImmutable('now');
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: $expiresAt,
                usedAt: $usedAt,
            );

            expect($token->getUsedAt())->toBe($usedAt);
        });
    });

    describe('isUsed', function () {
        it('returns false when token is not used', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('+1 hour'),
            );

            expect($token->isUsed())->toBeFalse();
        });

        it('returns true when token is used', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('+1 hour'),
                usedAt: new DateTimeImmutable('now'),
            );

            expect($token->isUsed())->toBeTrue();
        });
    });

    describe('isExpired', function () {
        it('returns false when token is not expired', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('+1 hour'),
            );

            expect($token->isExpired())->toBeFalse();
        });

        it('returns true when token is expired', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('-1 hour'),
            );

            expect($token->isExpired())->toBeTrue();
        });
    });

    describe('isValid', function () {
        it('returns true when token is valid (not used and not expired)', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('+1 hour'),
            );

            expect($token->isValid())->toBeTrue();
        });

        it('returns false when token is used', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('+1 hour'),
                usedAt: new DateTimeImmutable('now'),
            );

            expect($token->isValid())->toBeFalse();
        });

        it('returns false when token is expired', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('-1 hour'),
            );

            expect($token->isValid())->toBeFalse();
        });

        it('returns false when token is both used and expired', function () {
            $token = EmailVerificationToken::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                userId: '987e6543-e21b-12d3-a456-426614174000',
                token: 'verification-token-123',
                expiresAt: new DateTimeImmutable('-1 hour'),
                usedAt: new DateTimeImmutable('now'),
            );

            expect($token->isValid())->toBeFalse();
        });
    });
});
