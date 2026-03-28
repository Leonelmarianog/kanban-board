<?php

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
});
