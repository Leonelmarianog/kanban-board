<?php

use Modules\Domain\User\User;
use Modules\Domain\ValueObjects\HashedPassword;
use Modules\Infrastructure\Persistence\Mappers\UserMapper;
use Modules\Infrastructure\Persistence\Models\UserModel;

describe('UserMapper', function () {
    describe('toDomain', function () {
        it('maps model to domain entity', function () {
            $model = UserModel::factory()->make([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => Hash::make('password123'),
                'picture' => 'https://example.com/avatar.jpg',
                'bio' => 'Software developer',
            ]);
            $model->created_at = now();
            $model->updated_at = now();
            $model->deleted_at = null;

            $user = UserMapper::toDomain($model);

            expect($user->getId())->toBe('123e4567-e89b-12d3-a456-426614174000')
                ->and($user->getFirstName()->getValue())->toBe('John')
                ->and($user->getLastName()->getValue())->toBe('Doe')
                ->and($user->getEmail()->getValue())->toBe('john@example.com')
                ->and($user->getUsername()->getValue())->toBe('johndoe')
                ->and($user->getPicture())->toBe('https://example.com/avatar.jpg')
                ->and($user->getBio())->toBe('Software developer')
                ->and($user->getPassword()->verify('password123'))->toBeTrue()
                ->and($user->getCreatedAt())->not->toBeNull()
                ->and($user->getUpdatedAt())->not->toBeNull()
                ->and($user->getDeletedAt())->toBeNull();
        });

        it('maps model with soft delete to domain entity', function () {
            $model = UserModel::factory()->make([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => Hash::make('password123'),
            ]);
            $model->created_at = now();
            $model->updated_at = now();
            $model->deleted_at = now();

            $user = UserMapper::toDomain($model);

            expect($user->getDeletedAt())->not->toBeNull();
        });
    });

    describe('toModel', function () {
        it('maps domain entity to model', function () {
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
                picture: 'https://example.com/avatar.jpg',
                bio: 'Software developer',
            );

            $model = UserMapper::toModel($user);

            expect($model->id)->toBe('123e4567-e89b-12d3-a456-426614174000')
                ->and($model->first_name)->toBe('John')
                ->and($model->last_name)->toBe('Doe')
                ->and($model->email)->toBe('john@example.com')
                ->and($model->username)->toBe('johndoe')
                ->and($model->picture)->toBe('https://example.com/avatar.jpg')
                ->and($model->bio)->toBe('Software developer')
                ->and(Hash::check('password123', $model->password))->toBeTrue();
        });

        it('maps domain entity with null optional fields to model', function () {
            $user = User::create(
                id: '123e4567-e89b-12d3-a456-426614174000',
                firstName: 'John',
                lastName: 'Doe',
                email: 'john@example.com',
                password: HashedPassword::fromPlainText('password123'),
                username: 'johndoe',
            );

            $model = UserMapper::toModel($user);

            expect($model->picture)->toBeNull()
                ->and($model->bio)->toBeNull();
        });
    });

    describe('round-trip mapping', function () {
        it('preserves data when converting from model to domain and back', function () {
            $originalModel = UserModel::factory()->make([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
                'username' => 'johndoe',
                'password' => Hash::make('password123'),
                'picture' => 'https://example.com/avatar.jpg',
                'bio' => 'Software developer',
            ]);
            $originalModel->created_at = now();
            $originalModel->updated_at = now();
            $originalModel->deleted_at = null;

            $domain = UserMapper::toDomain($originalModel);
            $backToModel = UserMapper::toModel($domain);

            expect($backToModel->id)->toBe($originalModel->id)
                ->and($backToModel->first_name)->toBe($originalModel->first_name)
                ->and($backToModel->last_name)->toBe($originalModel->last_name)
                ->and($backToModel->email)->toBe($originalModel->email)
                ->and($backToModel->username)->toBe($originalModel->username)
                ->and($backToModel->picture)->toBe($originalModel->picture)
                ->and($backToModel->bio)->toBe($originalModel->bio);
        });
    });
});
