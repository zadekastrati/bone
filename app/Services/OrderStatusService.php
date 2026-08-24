<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusService
{
    /**
     * Statuses worth emailing the customer about — the ones they're actually
     * waiting on. Pending/Confirmed/Processing are internal housekeeping
     * states that don't need to interrupt someone's inbox.
     */
    private const NOTIFY_ON = [
        OrderStatus::Shipped,
        OrderStatus::Delivered,
        OrderStatus::Cancelled,
    ];

    /**
     * @param  array<string, mixed>  $attributes  Additional columns to update alongside status (payment_status, tracking_number, admin_notes, ...).
     */
    public function updateStatus(Order $order, OrderStatus $status, array $attributes = []): Order
    {
        $previousStatus = $order->status;

        $shippedAt = array_key_exists('shipped_at', $attributes) ? $attributes['shipped_at'] : $order->shipped_at;
        if ($status === OrderStatus::Shipped && $shippedAt === null && $order->shipped_at === null) {
            $shippedAt = now();
        }

        $order->update([...$attributes, 'status' => $status, 'shipped_at' => $shippedAt]);

        if ($previousStatus !== $status && in_array($status, self::NOTIFY_ON, true)) {
            $this->notifyCustomer($order);
        }

        return $order;
    }

    private function notifyCustomer(Order $order): void
    {
        try {
            $order->loadMissing('user');
            $recipient = $order->user?->email;

            if ($recipient === null) {
                return;
            }

            Mail::to($recipient)
                ->locale($order->user->locale ?? app()->getLocale())
                ->send(new OrderStatusUpdatedMail($order));
        } catch (\Throwable $e) {
            Log::error('Order status update email failed', [
                'order_id' => $order->id,
                'status' => $order->status->value,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
