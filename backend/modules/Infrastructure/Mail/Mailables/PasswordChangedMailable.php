<?php

namespace Modules\Infrastructure\Mail\Mailables;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Modules\Domain\User\User;

final class PasswordChangedMailable extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->getEmail()->getValue()],
            subject: 'Your Password Was Changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-changed',
            with: [
                'name' => $this->user->getFirstName()->getValue(),
            ],
        );
    }
}
