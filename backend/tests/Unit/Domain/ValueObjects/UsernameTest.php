<?php

use Modules\Domain\ValueObjects\Exceptions\InvalidUsernameFormatException;
use Modules\Domain\ValueObjects\Exceptions\InvalidUsernameLengthException;
use Modules\Domain\ValueObjects\Username;

describe('Username Value Object', function () {
    describe('constructor', function () {
        it('creates a valid username', function () {
            $username = new Username('johndoe');

            expect($username->getValue())->toBe('johndoe');
        });

        it('creates a valid username with underscores', function () {
            $username = new Username('john_doe_123');

            expect($username->getValue())->toBe('john_doe_123');
        });

        it('creates a valid username with minimum length', function () {
            $username = new Username('abc');

            expect($username->getValue())->toBe('abc');
        });

        it('creates a valid username with maximum length', function () {
            $username = new Username(str_repeat('a', 50));

            expect($username->getValue())->toBe(str_repeat('a', 50));
        });

        it('throws exception for username shorter than 3 characters', function () {
            new Username('ab');
        })->throws(InvalidUsernameLengthException::class);

        it('throws exception for username longer than 50 characters', function () {
            new Username(str_repeat('a', 51));
        })->throws(InvalidUsernameLengthException::class);

        it('throws exception for username with invalid characters', function () {
            new Username('john@doe');
        })->throws(InvalidUsernameFormatException::class);

        it('throws exception for username with spaces', function () {
            new Username('john doe');
        })->throws(InvalidUsernameFormatException::class);

        it('throws exception for empty username', function () {
            new Username('');
        })->throws(InvalidUsernameLengthException::class);
    });

    describe('equals', function () {
        it('returns true for same usernames', function () {
            $username1 = new Username('johndoe');
            $username2 = new Username('johndoe');

            expect($username1->equals($username2))->toBeTrue();
        });

        it('returns false for different usernames', function () {
            $username1 = new Username('johndoe');
            $username2 = new Username('janedoe');

            expect($username1->equals($username2))->toBeFalse();
        });
    });
});
