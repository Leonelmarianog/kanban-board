<?php

namespace Modules\Application\UseCases\Auth\SendVerificationEmail;

use Modules\Domain\User\User;

interface SendVerificationEmailRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function createVerificationToken(User $user): string;
}
