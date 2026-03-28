<?php

namespace Modules\Infrastructure\Persistence\Mappers;

use DateTimeImmutable;
use Modules\Domain\Auth\EmailVerificationToken;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;

final class EmailVerificationTokenMapper
{
    public static function toDomain(EmailVerificationTokenModel $model): EmailVerificationToken
    {
        return EmailVerificationToken::create(
            id: $model->id,
            userId: $model->user_id,
            token: $model->token,
            expiresAt: new DateTimeImmutable($model->expires_at->toIso8601String()),
            usedAt: $model->used_at ? new DateTimeImmutable($model->used_at->toIso8601String()) : null,
        );
    }
}
