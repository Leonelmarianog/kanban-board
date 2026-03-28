<?php

namespace Modules\Infrastructure\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

final readonly class LaravelMailer implements MailerInterface
{
    public function queue(Mailable $mailable): void
    {
        Mail::queue($mailable);
    }
}
