<?php

namespace Modules\Infrastructure\Persistence\Mappers;

use DateTimeImmutable;
use Modules\Domain\Auth\EmailChangeToken;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;

final class EmailChangeTokenMapper
{
    public static function toDomain(EmailChangeTokenModel $model): EmailChangeToken
    {
        return EmailChangeToken::create(
            id: $model->id,
            userId: $model->user_id,
            currentEmail: $model->current_email,
            newEmail: $model->new_email,
            token: $model->token,
            expiresAt: new DateTimeImmutable($model->expires_at->toIso8601String()),
            confirmedAt: $model->confirmed_at ? new DateTimeImmutable($model->confirmed_at->toIso8601String()) : null,
        );
    }
}
