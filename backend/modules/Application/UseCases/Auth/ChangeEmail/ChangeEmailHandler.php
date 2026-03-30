<?php

namespace Modules\Application\UseCases\Auth\ChangeEmail;

use DateTimeImmutable;
use Modules\Application\UseCases\Auth\ChangeEmail\Exceptions\EmailAlreadyInUseException;
use Modules\Application\UseCases\Auth\ChangeEmail\Exceptions\SameEmailException;
use Modules\Domain\Auth\EmailChangeToken;
use Modules\Infrastructure\Mail\Mailables\EmailChangeNotificationMailable;
use Modules\Infrastructure\Mail\Mailables\EmailChangeVerificationMailable;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\Persistence\TransactionInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;
use Str;

final readonly class ChangeEmailHandler
{
    private const TOKEN_LENGTH = 64;

    public function __construct(
        private ChangeEmailRepositoryInterface $repository,
        private MailerInterface $mailer,
        private SignedUrlInterface $urlSigner,
        private TransactionInterface $transaction,
    ) {}

    public function execute(ChangeEmailRequestDto $request): ChangeEmailResponseDto
    {
        $user = $this->repository->findById($request->userId);

        if ($user === null) {
            return new ChangeEmailResponseDto(
                message: 'If your account exists, you will receive an email change confirmation.',
            );
        }

        if ($user->getEmail()->getValue() === $request->newEmail) {
            throw new SameEmailException;
        }

        if ($this->repository->emailExists($request->newEmail, $request->userId)) {
            throw new EmailAlreadyInUseException($request->newEmail);
        }

        $pendingToken = $this->repository->findPendingByUserId($request->userId);

        $token = $this->createToken();
        $expiresAt = $this->getExpirationTime();

        $emailChangeToken = EmailChangeToken::create(
            id: Str::uuid()->toString(),
            userId: $user->getId(),
            currentEmail: $user->getEmail()->getValue(),
            newEmail: $request->newEmail,
            token: $token,
            expiresAt: $expiresAt,
        );

        $this->transaction->execute(function () use ($pendingToken, $emailChangeToken): void {
            if ($pendingToken !== null) {
                $this->repository->deleteToken($pendingToken->getId());
            }

            $this->repository->saveToken($emailChangeToken);
        });

        $verificationUrl = $this->generateVerificationUrl($token);
        $cancelUrl = $this->generateCancelUrl($token);
        $expirationSeconds = config('verification.email_change_token_expiration_seconds', 3600);

        $this->mailer->queue(new EmailChangeVerificationMailable(
            newEmail: $request->newEmail,
            verificationUrl: $verificationUrl,
            expirationMinutes: (int) ($expirationSeconds / 60),
        ));

        $this->mailer->queue(new EmailChangeNotificationMailable(
            user: $user,
            newEmail: $request->newEmail,
            cancelUrl: $cancelUrl,
            expirationMinutes: (int) ($expirationSeconds / 60),
        ));

        return new ChangeEmailResponseDto(
            message: 'A verification email has been sent to your new email address.',
        );
    }

    private function createToken(): string
    {
        return Str::random(self::TOKEN_LENGTH);
    }

    private function getExpirationTime(): DateTimeImmutable
    {
        $expirationSeconds = config('verification.email_change_token_expiration_seconds', 3600);

        return new DateTimeImmutable('+'.(int) $expirationSeconds.' seconds');
    }

    private function generateVerificationUrl(string $token): string
    {
        $baseUrl = config('app.frontend_url').'/email-change/confirm';
        $urlWithToken = $baseUrl.'?token='.$token;
        $expirationSeconds = config('verification.email_change_token_expiration_seconds', 3600);

        return $this->urlSigner->sign($urlWithToken, $expirationSeconds);
    }

    private function generateCancelUrl(string $token): string
    {
        $baseUrl = config('app.frontend_url').'/email-change/cancel';
        $urlWithToken = $baseUrl.'?token='.$token;
        $expirationSeconds = config('verification.email_change_token_expiration_seconds', 3600);

        return $this->urlSigner->sign($urlWithToken, $expirationSeconds);
    }
}
