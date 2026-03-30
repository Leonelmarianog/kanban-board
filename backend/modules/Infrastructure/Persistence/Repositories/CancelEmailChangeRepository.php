<?php

namespace Modules\Infrastructure\Persistence\Repositories;

use Modules\Application\UseCases\Auth\CancelEmailChange\CancelEmailChangeRepositoryInterface;
use Modules\Domain\Auth\EmailChangeToken;
use Modules\Infrastructure\Persistence\Mappers\EmailChangeTokenMapper;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;

final readonly class CancelEmailChangeRepository implements CancelEmailChangeRepositoryInterface
{
    public function __construct(
        private EmailChangeTokenModel $tokenModel,
    ) {}

    public function findValidToken(string $token): ?EmailChangeToken
    {
        $model = $this->tokenModel
            ->where('token', $token)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($model === null) {
            return null;
        }

        return EmailChangeTokenMapper::toDomain($model);
    }

    public function deleteToken(string $tokenId): void
    {
        $this->tokenModel->where('id', $tokenId)->delete();
    }
}
