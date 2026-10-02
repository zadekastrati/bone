<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
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
     * Statuses an order can be cancelled FROM where its stock is still
     * genuinely sitting reserved rather than physically out the door. Once
     * an order has shipped or been delivered, cancelling it afterwards
     * (e.g. recording a return) must never auto-increment stock — the item
     * isn't back in the warehouse just because the status changed, and
     * doing so would let it be oversold on nothing but a label update.
     */
    private const CANCELLABLE_WITH_STOCK_RELEASE = [
        OrderStatus::Pending,
        OrderStatus::Confirmed,
        OrderStatus::Processing,
    ];

    /**
     * @param  array<string, mixed>  $attributes  Additional columns to update alongside status (payment_status, admin_notes, ...).
     */
    public function updateStatus(Order $order, OrderStatus $status, array $attributes = []): Order
    {
        $previousStatus = $order->status;

        $shippedAt = array_key_exists('shipped_at', $attributes) ? $attributes['shipped_at'] : $order->shipped_at;
        if ($status === OrderStatus::Shipped && $shippedAt === null && $order->shipped_at === null) {
            $shippedAt = now();
        }

        $values = [...$attributes, 'status' => $status, 'shipped_at' => $shippedAt];

        if ($status === OrderStatus::Cancelled && in_array($previousStatus, self::CANCELLABLE_WITH_STOCK_RELEASE, true)) {
            $applied = $this->cancelAndReleaseStock($order, $values, $previousStatus);
        } else {
            $order->update($values);
            $applied = true;
        }

        if ($applied && $previousStatus !== $status && in_array($status, self::NOTIFY_ON, true)) {
            $this->notifyCustomer($order);
        }

        return $order;
    }

    /**
     * Mirrors QuipuPaymentService::declineAndReleaseStock() and
     * ExpireAbandonedCardOrdersCommand's own release logic — an order
     * being cancelled before it ever shipped means its reserved stock (and
     * any claimed discount code) is just sitting unavailable for no reason.
     * This is an admin-initiated cancellation rather than an automatic one,
     * but the stock doesn't know the difference, so it's released the same
     * safe way. The conditional update is a guard against this running
     * twice for the same cancellation (e.g. a double click on "Update
     * status") — it only restores stock the first time it actually applies.
     *
     * @param  array<string, mixed>  $values
     */
    private function cancelAndReleaseStock(Order $order, array $values, OrderStatus $previousStatus): bool
    {
        $order->loadMissing('items');

        $applied = DB::transaction(function () use ($order, $values, $previousStatus): bool {
            $updated = Order::query()
                ->whereKey($order->id)
                ->where('status', $previousStatus)
                ->update($values);

            if ($updated === 0) {
                return false;
            }

            foreach ($order->items as $item) {
                ProductVariant::query()
                    ->whereKey($item->product_variant_id)
                    ->increment('stock_quantity', $item->quantity);
            }

            if ($order->discount_code_id !== null) {
                DiscountCode::query()
                    ->whereKey($order->discount_code_id)
                    ->where('used_by_order_id', $order->id)
                    ->update(['used_at' => null, 'used_by_order_id' => null]);
            }

            Log::info('Order cancelled before shipping — released its reserved stock', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return true;
        });

        $order->refresh();

        return $applied;
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
