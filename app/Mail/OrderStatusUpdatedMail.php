<?php

namespace App\Mail;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable implements ShouldQueueAfterCommit
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Order $order
    ) {}

    public function envelope(): Envelope
    {
        $name = (string) config('app.name', 'Store');
        $number = $this->order->order_number;

        $subject = match ($this->order->status) {
            OrderStatus::Shipped => __('Order :number has shipped', ['number' => $number]),
            OrderStatus::Delivered => __('Order :number has been delivered', ['number' => $number]),
            OrderStatus::Cancelled => __('Order :number was cancelled', ['number' => $number]),
            default => __('Order :number updated', ['number' => $number]),
        };

        return new Envelope(subject: $name.' — '.$subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order-status-updated-html',
        );
    }
}
