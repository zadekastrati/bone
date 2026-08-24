<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewOrderAdminMail extends Mailable implements ShouldQueueAfterCommit
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('New order :number — :total', [
                'number' => $this->order->order_number,
                'total' => config('store.currency_symbol').number_format((float) $this->order->total, 2),
            ]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-order-admin-html',
        );
    }
}
