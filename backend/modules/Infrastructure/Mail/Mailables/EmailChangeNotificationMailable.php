<?php

namespace Modules\Infrastructure\Mail\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Domain\User\User;

final class EmailChangeNotificationMailable extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly string $newEmail,
        public readonly string $cancelUrl,
        public readonly int $expirationMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->getEmail()->getValue()],
            subject: 'Email Change Request',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-notification',
            with: [
                'name' => $this->user->getFirstName()->getValue(),
                'newEmail' => $this->maskEmail($this->newEmail),
                'cancelUrl' => $this->cancelUrl,
                'expirationMinutes' => $this->expirationMinutes,
            ],
        );
    }

    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $localPart = $parts[0];
        $domain = $parts[1];

        $maskedLocal = substr($localPart, 0, 2).str_repeat('*', max(strlen($localPart) - 2, 0));

        return $maskedLocal.'@'.$domain;
    }
}
