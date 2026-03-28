<?php

use Modules\Infrastructure\Persistence\Mappers\EmailVerificationTokenMapper;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;

describe('EmailVerificationTokenMapper', function () {
    describe('toDomain', function () {
        it('maps model to domain entity', function () {
            $model = EmailVerificationTokenModel::factory()->make([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'user_id' => '987e6543-e21b-12d3-a456-426614174000',
                'token' => 'verification-token-123',
                'expires_at' => now()->addHour(),
                'used_at' => null,
            ]);

            $token = EmailVerificationTokenMapper::toDomain($model);

            expect($token->getId())->toBe('123e4567-e89b-12d3-a456-426614174000')
                ->and($token->getUserId())->toBe('987e6543-e21b-12d3-a456-426614174000')
                ->and($token->getToken())->toBe('verification-token-123')
                ->and($token->getExpiresAt())->not->toBeNull()
                ->and($token->getUsedAt())->toBeNull();
        });

        it('maps model with used_at to domain entity', function () {
            $usedAt = now();
            $model = EmailVerificationTokenModel::factory()->make([
                'id' => '123e4567-e89b-12d3-a456-426614174000',
                'user_id' => '987e6543-e21b-12d3-a456-426614174000',
                'token' => 'verification-token-123',
                'expires_at' => now()->addHour(),
                'used_at' => $usedAt,
            ]);

            $token = EmailVerificationTokenMapper::toDomain($model);

            expect($token->getUsedAt())->not->toBeNull();
        });
    });
});
