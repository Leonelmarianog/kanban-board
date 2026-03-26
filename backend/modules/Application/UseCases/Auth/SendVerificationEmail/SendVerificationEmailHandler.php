<?php

namespace Modules\Application\UseCases\Auth\SendVerificationEmail;

use Modules\Infrastructure\Mail\Mailables\VerificationMailable;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;

final readonly class SendVerificationEmailHandler
{
    public function __construct(
        private SendVerificationEmailRepositoryInterface $repository,
        private MailerInterface $mailer,
        private SignedUrlInterface $urlSigner,
    ) {}

    public function execute(SendVerificationEmailRequestDto $request): SendVerificationEmailResponseDto
    {
        $user = $this->repository->findByEmail($request->email);

        if ($user === null) {
            return new SendVerificationEmailResponseDto(
                message: 'If your email is registered, you will receive a verification link.',
            );
        }

        if ($user->isEmailVerified()) {
            return new SendVerificationEmailResponseDto(
                message: 'Your email is already verified.',
            );
        }

        $token = $this->repository->createVerificationToken($user);
        $verificationUrl = $this->generateVerificationUrl($token);
        $expirationSeconds = config('verification.token_expiration_seconds', 900);

        $this->mailer->queue(new VerificationMailable(
            user: $user,
            verificationUrl: $verificationUrl,
            expirationMinutes: (int) ($expirationSeconds / 60),
        ));

        return new SendVerificationEmailResponseDto(
            message: 'If your email is registered, you will receive a verification link.',
        );
    }

    private function generateVerificationUrl(string $token): string
    {
        $baseUrl = config('app.frontend_url').'/verify-email';
        $urlWithToken = $baseUrl.'?token='.$token;
        $expirationSeconds = config('verification.token_expiration_seconds', 900);

        return $this->urlSigner->sign($urlWithToken, $expirationSeconds);
    }
}
