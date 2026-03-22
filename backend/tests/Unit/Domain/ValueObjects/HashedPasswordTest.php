<?php

use Modules\Domain\ValueObjects\HashedPassword;

describe('HashedPassword Value Object', function () {
    describe('fromPlainText', function () {
        it('creates a hashed password from plain text', function () {
            $hashedPassword = HashedPassword::fromPlainText('password123');

            expect($hashedPassword->getHashedValue())->toBeString()
                ->and(strlen($hashedPassword->getHashedValue()))->toBeGreaterThan(0);
        });

        it('creates different hashes for same password', function () {
            $hashedPassword1 = HashedPassword::fromPlainText('password123');
            $hashedPassword2 = HashedPassword::fromPlainText('password123');

            // Passwords are hashed with different salts, so they should be different
            expect($hashedPassword1->getHashedValue())->not->toBe($hashedPassword2->getHashedValue());
        });
    });

    describe('fromHash', function () {
        it('creates a hashed password from existing hash', function () {
            $hash = '$2y$12$abcdefghijklmnopqrstuvwxABCDEFGHIJ1234567890abcdefghijklmno';

            $hashedPassword = HashedPassword::fromHash($hash);

            expect($hashedPassword->getHashedValue())->toBe($hash);
        });
    });

    describe('verify', function () {
        it('returns true for correct password', function () {
            $hashedPassword = HashedPassword::fromPlainText('password123');

            expect($hashedPassword->verify('password123'))->toBeTrue();
        });

        it('returns false for incorrect password', function () {
            $hashedPassword = HashedPassword::fromPlainText('password123');

            expect($hashedPassword->verify('wrongpassword'))->toBeFalse();
        });
    });
});
