<?php

namespace Modules\Infrastructure\Mail;

use Illuminate\Mail\Mailable;

interface MailerInterface
{
    /**
     * Queue a mailable for sending.
     */
    public function queue(Mailable $mailable): void;
}
