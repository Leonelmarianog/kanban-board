<?php

use Modules\Domain\ValueObjects\Exceptions\InvalidFullNameLengthException;
use Modules\Domain\ValueObjects\UserFullName;

describe('UserFullName Value Object', function () {
    describe('constructor', function () {
        it('creates a valid name', function () {
            $name = new UserFullName('John');

            expect($name->getValue())->toBe('John');
        });

        it('creates a valid name with maximum length', function () {
            $name = new UserFullName(str_repeat('a', 255));

            expect($name->getValue())->toBe(str_repeat('a', 255));
        });

        it('throws exception for empty name', function () {
            new UserFullName('');
        })->throws(InvalidFullNameLengthException::class);

        it('throws exception for name longer than 255 characters', function () {
            new UserFullName(str_repeat('a', 256));
        })->throws(InvalidFullNameLengthException::class);
    });

    describe('equals', function () {
        it('returns true for same names', function () {
            $name1 = new UserFullName('John');
            $name2 = new UserFullName('John');

            expect($name1->equals($name2))->toBeTrue();
        });

        it('returns false for different names', function () {
            $name1 = new UserFullName('John');
            $name2 = new UserFullName('Jane');

            expect($name1->equals($name2))->toBeFalse();
        });
    });
});
