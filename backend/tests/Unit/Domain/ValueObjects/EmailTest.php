<?php

use Modules\Domain\ValueObjects\Email;
use Modules\Domain\ValueObjects\Exceptions\InvalidEmailException;

describe('Email Value Object', function () {
    describe('constructor', function () {
        it('creates a valid email', function () {
            $email = new Email('john@example.com');

            expect($email->getValue())->toBe('john@example.com');
        });

        it('throws exception for invalid email', function () {
            new Email('invalid-email');
        })->throws(InvalidEmailException::class);

        it('throws exception for empty email', function () {
            new Email('');
        })->throws(InvalidEmailException::class);
    });

    describe('equals', function () {
        it('returns true for same emails', function () {
            $email1 = new Email('john@example.com');
            $email2 = new Email('john@example.com');

            expect($email1->equals($email2))->toBeTrue();
        });

        it('returns false for different emails', function () {
            $email1 = new Email('john@example.com');
            $email2 = new Email('jane@example.com');

            expect($email1->equals($email2))->toBeFalse();
        });
    });
});
