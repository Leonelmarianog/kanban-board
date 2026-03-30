<?php

namespace Modules\Infrastructure\Mail\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class EmailChangeVerificationMailable extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $newEmail,
        public readonly string $verificationUrl,
        public readonly int $expirationMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->newEmail],
            subject: 'Confirm Your Email Change',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-verification',
            with: [
                'verificationUrl' => $this->verificationUrl,
                'expirationMinutes' => $this->expirationMinutes,
            ],
        );
    }
}
