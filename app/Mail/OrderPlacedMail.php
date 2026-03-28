<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable implements ShouldQueueAfterCommit
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        $name = (string) config('app.name', 'Store');

        return new Envelope(
            subject: $name.' — Order '.$this->order->order_number.' confirmed',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-placed-html',
        );
    }
}
