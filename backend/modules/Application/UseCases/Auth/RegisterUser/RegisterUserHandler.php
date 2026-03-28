<?php

namespace Modules\Application\UseCases\Auth\RegisterUser;

use Illuminate\Support\Str;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\EmailAlreadyExistsException;
use Modules\Application\UseCases\Auth\RegisterUser\Exceptions\UsernameAlreadyExistsException;
use Modules\Domain\User\User;
use Modules\Domain\ValueObjects\HashedPassword;
use Modules\Infrastructure\Mail\Mailables\VerificationMailable;
use Modules\Infrastructure\Mail\MailerInterface;
use Modules\Infrastructure\SignedUrl\SignedUrlInterface;

final readonly class RegisterUserHandler
{
    public function __construct(
        private RegisterUserRepositoryInterface $repository,
        private MailerInterface $mailer,
        private SignedUrlInterface $urlSigner,
    ) {}

    public function execute(RegisterUserRequestDto $request): RegisterUserResponseDto
    {
        if ($this->repository->emailExists($request->email)) {
            throw new EmailAlreadyExistsException($request->email);
        }

        if ($this->repository->usernameExists($request->username)) {
            throw new UsernameAlreadyExistsException($request->username);
        }

        $user = User::create(
            id: Str::uuid()->toString(),
            firstName: $request->firstName,
            lastName: $request->lastName,
            email: $request->email,
            password: HashedPassword::fromPlainText($request->password),
            username: $request->username,
        );

        $savedUser = $this->repository->save($user);
        $token = $this->repository->createVerificationToken($savedUser);
        $verificationUrl = $this->generateVerificationUrl($token);
        $expirationSeconds = config('verification.token_expiration_seconds', 900);

        $this->mailer->queue(new VerificationMailable(
            user: $savedUser,
            verificationUrl: $verificationUrl,
            expirationMinutes: (int) ($expirationSeconds / 60),
        ));

        return new RegisterUserResponseDto(
            message: 'Registration successful. Please check your email to verify your account.',
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
