<?php

namespace App\Console\Commands;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Services\QuipuPaymentService;
use Illuminate\Console\Command;

class ExpireAbandonedCardOrdersCommand extends Command
{
    protected $signature = 'orders:expire-abandoned-card-payments';

    protected $description = 'Cancel Pending card orders abandoned long enough to be safe, releasing their reserved stock exactly once.';

    public function __construct(private readonly QuipuPaymentService $quipu)
    {
        parent::__construct();
    }

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
            // Re-confirms with Quipu one more time before giving up on this
            // order — not just a blind timeout. If Quipu can't be reached
            // right now, this leaves the order Pending for the next sweep to
            // retry, rather than risk cancelling (and releasing the stock
            // of) an order that was actually paid. A definite "paid" or
            // "failed" answer is handled entirely by confirmPayment() itself
            // (QuipuPaymentService::declineAndReleaseStock() mirrors exactly
            // what this command used to do inline) — nothing further needed
            // here either way.
            if ($this->quipu->confirmPayment($order) === PaymentStatus::Failed) {
                $expired++;
            }
        }

        $this->info("Expired {$expired} abandoned card order(s).");

        return self::SUCCESS;
    }
}
