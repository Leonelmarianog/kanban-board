<?php

namespace Modules\Application\UseCases\Auth\LogoutUser;

final readonly class LogoutUserHandler
{
    public function __construct(
        private LogoutUserRepositoryInterface $repository,
    ) {}

    public function execute(string $userId, int $tokenId): void
    {
        $this->repository->revokeCurrentToken($userId, $tokenId);
    }
}
