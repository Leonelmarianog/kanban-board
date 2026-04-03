<?php

namespace Modules\Application\UseCases\Auth\ChangePassword;

use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\InvalidCurrentPasswordException;
use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\SamePasswordException;
use Modules\Application\UseCases\Auth\ChangePassword\Exceptions\UserNotFoundException;
use Modules\Domain\ValueObjects\HashedPassword;
use Modules\Infrastructure\Mail\Mailables\PasswordChangedMailable;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\Persistence\TransactionInterface;

final readonly class ChangePasswordHandler
{
    public function __construct(
        private ChangePasswordRepositoryInterface $repository,
        private TransactionInterface $transaction,
        private MailerInterface $mailer,
    ) {}

    public function execute(ChangePasswordRequestDto $request): ChangePasswordResponseDto
    {
        $user = $this->repository->findById($request->userId);

        if ($user === null) {
            throw new UserNotFoundException($request->userId);
        }

        if (! $user->getPassword()->verify($request->currentPassword)) {
            throw new InvalidCurrentPasswordException;
        }

        if ($user->getPassword()->verify($request->newPassword)) {
            throw new SamePasswordException;
        }

        $newPassword = HashedPassword::fromPlainText($request->newPassword);
        $user->changePassword($newPassword);

        $this->transaction->execute(function () use ($user, $request) {
            $this->repository->updatePassword($user);
            $this->repository->revokeOtherTokens($user->getId(), $request->currentTokenId);
        });

        $this->mailer->queue(new PasswordChangedMailable($user));

        return new ChangePasswordResponseDto(
            message: 'Password changed successfully. You have been logged out from other devices.',
        );
    }
}
