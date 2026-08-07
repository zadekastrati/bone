<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailChangeRequestedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $appName,
        public string $newEmail,
        public string $requestedAt,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->appName.' — Your account email is being changed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.email-change-requested-html',
        );
    }
}
