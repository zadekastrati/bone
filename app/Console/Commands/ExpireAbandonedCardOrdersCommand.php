<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpireAbandonedCardOrdersCommand extends Command
{
    protected $signature = 'orders:expire-abandoned-card-payments';

    protected $description = 'Cancel Pending card orders abandoned long enough to be safe, releasing their reserved stock exactly once.';

    public function handle(): int
    {
        $minutes = (int) config('store.orders.abandoned_card_payment_minutes', 60);
        $cutoff = now()->subMinutes($minutes);

        $candidates = Order::query()
            ->where('payment_method', PaymentMethod::Card)
            ->where('payment_status', PaymentStatus::Pending)
            ->where('created_at', '<', $cutoff)
            ->with('items')
            ->get();

        $expired = 0;

        foreach ($candidates as $order) {
            if ($this->expire($order)) {
                $expired++;
            }
        }

        $this->info("Expired {$expired} abandoned card order(s).");

        return self::SUCCESS;
    }

    /**
     * The conditional update below is the only thing that decides whether
     * this order actually gets expired, and it only succeeds while the row
     * is still Pending at that exact instant — so stock is restored once
     * and only once, this is safe if two runs of this command ever overlap,
     * and a payment that resolves the order concurrently (via
     * QuipuPaymentService::confirmPayment(), which uses the same guarded
     * pattern) always wins instead of being silently overwritten.
     */
    private function expire(Order $order): bool
    {
        return DB::transaction(function () use ($order): bool {
            $updated = Order::query()
                ->whereKey($order->id)
                ->where('payment_status', PaymentStatus::Pending)
                ->update([
                    'status' => OrderStatus::Cancelled,
                    'payment_status' => PaymentStatus::Failed,
                ]);

            if ($updated === 0) {
                return false;
            }

            foreach ($order->items as $item) {
                ProductVariant::query()
                    ->whereKey($item->product_variant_id)
                    ->increment('stock_quantity', $item->quantity);
            }

            Log::info('Expired an abandoned card order and released its reserved stock', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]);

            return true;
        });
    }
}
