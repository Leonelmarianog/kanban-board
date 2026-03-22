<?php

use Modules\Domain\User\User;
use Modules\Domain\ValueObjects\HashedPassword;

describe('User Entity', function () {
    describe('create', function () {
        it('creates a user with valid data', function () {
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
            );

            expect($user->getId())->toBe('123e4567-e89b-12d3-a456-426614174000')
                ->and($user->getFirstName()->getValue())->toBe('John')
                ->and($user->getLastName()->getValue())->toBe('Doe')
                ->and($user->getEmail()->getValue())->toBe('john@example.com')
                ->and($user->getUsername()->getValue())->toBe('johndoe')
                ->and($user->getPicture())->toBeNull()
                ->and($user->getBio())->toBeNull()
                ->and($user->getDeletedAt())->toBeNull();
        });

        it('creates a user with optional fields', function () {
            $deletedAt = new DateTimeImmutable('2024-01-01 00:00:00');
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
                picture: 'https://example.com/avatar.jpg',
                bio: 'Software developer',
                deletedAt: $deletedAt,
            );

            expect($user->getPicture())->toBe('https://example.com/avatar.jpg')
                ->and($user->getBio())->toBe('Software developer')
                ->and($user->getDeletedAt()->format('Y-m-d'))->toBe('2024-01-01');
        });

        it('sets createdAt and updatedAt to the same value on creation', function () {
            $beforeCreation = new DateTimeImmutable;
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
            );
            $afterCreation = new DateTimeImmutable;

            expect($user->getCreatedAt())->toBeGreaterThan($beforeCreation->modify('-1 second'))
                ->and($user->getCreatedAt())->toBeLessThan($afterCreation->modify('+1 second'))
                ->and($user->getCreatedAt()->format('Y-m-d H:i:s'))->toBe($user->getUpdatedAt()->format('Y-m-d H:i:s'));
        });

        it('accepts custom createdAt and updatedAt', function () {
            $customTime = new DateTimeImmutable('2024-06-15 12:30:00');
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
                createdAt: $customTime,
                updatedAt: $customTime,
            );

            expect($user->getCreatedAt()->format('Y-m-d H:i:s'))->toBe('2024-06-15 12:30:00')
                ->and($user->getUpdatedAt()->format('Y-m-d H:i:s'))->toBe('2024-06-15 12:30:00');
        });

        it('returns correct password hash', function () {
            $password = HashedPassword::fromPlainText('password123');
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: $password,
                username: 'johndoe',
            );

            expect($user->getPassword())->toBe($password)
                ->and($user->getPassword()->verify('password123'))->toBeTrue();
        });
    });
});
