<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Mail\NewOrderAdminMail;
use App\Mail\OrderPlacedMail;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutService
{
    public function __construct(
        private readonly CartService $cart,
        private readonly TelegramNotifier $telegram
    ) {}

    /**
     * @param  array{
     *     shipping_first_name: string,
     *     shipping_last_name: string,
     *     shipping_phone: string,
     *     shipping_street: string,
     *     shipping_building: ?string,
     *     shipping_city: string,
     *     shipping_region: ?string,
     *     shipping_postal_code: ?string,
     *     shipping_country: string,
     *     shipping_delivery_notes: ?string,
     *     payment_method: \App\Enums\PaymentMethod,
     *     customer_notes: ?string
     * }  $data
     */
    public function placeOrder(?User $user, array $data): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw new \InvalidArgumentException(__('Your cart is empty.'));
        }

        $order = DB::transaction(function () use ($user, $data, $lines) {
            $subtotal = $this->cart->subtotal();
            $shipping = $this->shippingAmountForCountry($data['shipping_country'], $subtotal);

            $email = $user?->email ?? ($data['guest_email'] ?? null);
            $discountCode = $this->resolveDiscountCode($data['discount_code'] ?? null, $user, $email);
            $discountAmount = $discountCode !== null
                ? bcdiv(bcmul($subtotal, (string) $discountCode->percent_off, 4), '100', 2)
                : '0.00';

            $order = Order::create([
                'user_id' => $user?->id,
                // Only meaningful for guest orders — an account's own email
                // is always used instead when one is logged in.
                'guest_email' => $user === null ? ($data['guest_email'] ?? null) : null,
                'status' => OrderStatus::Pending,
                'payment_method' => $data['payment_method'],
                'payment_status' => PaymentStatus::Pending,
                'shipping_first_name' => $data['shipping_first_name'],
                'shipping_last_name' => $data['shipping_last_name'],
                'shipping_phone' => $data['shipping_phone'],
                'shipping_street' => $data['shipping_street'],
                'shipping_building' => $data['shipping_building'] ?? null,
                'shipping_city' => $data['shipping_city'],
                'shipping_region' => $data['shipping_region'] ?? null,
                'shipping_postal_code' => $data['shipping_postal_code'] ?? '',
                'shipping_country' => strtoupper($data['shipping_country']),
                'shipping_delivery_notes' => $data['shipping_delivery_notes'] ?? null,
                'subtotal' => $subtotal,
                'discount_code_id' => $discountCode?->id,
                'discount_amount' => $discountAmount,
                'shipping_amount' => $shipping,
                'total' => bcsub(bcadd($subtotal, $shipping, 2), $discountAmount, 2),
                'customer_notes' => $data['customer_notes'] ?? null,
            ]);

            // Claims the code for this order only if it's still unused at this
            // exact instant — the FormRequest already checked this, but that
            // check and this write aren't atomic with each other, so two
            // people submitting the same code at the same moment could both
            // pass validation. Only one of them can win this update; the
            // other's order rolls back entirely (transaction) and they see a
            // normal error instead of two orders silently sharing one code.
            if ($discountCode !== null) {
                $claimed = DiscountCode::query()
                    ->whereKey($discountCode->id)
                    ->whereNull('used_at')
                    ->update(['used_at' => now(), 'used_by_order_id' => $order->id]);

                if ($claimed === 0) {
                    throw new \RuntimeException(__('This discount code has already been used.'));
                }
            }

            $variantIds = $lines
                ->map(fn (array $line): int => $line['variant']->id)
                ->unique()
                ->sort()
                ->values()
                ->all();

            $lockedVariants = ProductVariant::query()
                ->whereIn('id', $variantIds)
                ->orderBy('id')
                ->with('product')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lines as $line) {
                $variantId = $line['variant']->id;
                $variant = $lockedVariants->get($variantId);
                if ($variant === null) {
                    throw new \RuntimeException(__('A product in your cart is no longer available.'));
                }

                $qty = $line['quantity'];

                if (! $variant->product->is_active || $variant->product->trashed()) {
                    throw new \RuntimeException(__('A product in your cart is no longer available.'));
                }

                if (! $variant->isInStock($qty)) {
                    throw new \RuntimeException(__('Insufficient stock for :product (:color / :size).', [
                        'product' => $variant->product->name,
                        'color' => $variant->color,
                        'size' => $variant->size,
                    ]));
                }

                $unit = (string) $variant->product->price;
                $lineTotal = bcmul($unit, (string) $qty, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
                    'product_id' => $variant->product_id,
                    'product_name' => $variant->product->name,
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'sku' => $variant->sku,
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $lineTotal,
                ]);

                $variant->decrement('stock_quantity', $qty);
            }

            $this->cart->clear();

            return $order->load('items');
        });

        // A guest only has an email on file if they chose to enter one —
        // that field is optional, so there may be nothing to send this to.
        $recipient = $user?->email ?? $order->guest_email;

        // Card orders aren't confirmed yet at this point (payment_status is
        // still Pending) — the confirmation email is sent once payment
        // actually succeeds instead, so it can include the approval code,
        // card brand, and last 4 digits the bank requires on the invoice.
        if ($recipient !== null && $order->payment_method !== PaymentMethod::Card) {
            try {
                Mail::to($recipient)->locale($user?->locale ?? app()->getLocale())->send(new OrderPlacedMail($order));
            } catch (\Throwable $e) {
                Log::error('Order confirmation email failed', [
                    'order_id' => $order->id,
                    'user_id' => $user?->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $notifyAddress = config('store.notification_email') ?: config('mail.from.address');

        if ($notifyAddress) {
            try {
                Mail::to($notifyAddress)->send(new NewOrderAdminMail($order));
            } catch (\Throwable $e) {
                Log::error('New order admin notification failed', [
                    'order_id' => $order->id,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        $this->telegram->sendNewOrderAlert($order);

        return $order;
    }

    /**
     * placeOrder() already committed the order, decremented stock, and
     * cleared the cart by the time the Quipu gateway call can fail —
     * without this, the customer is left staring at an empty cart with no
     * idea whether they were charged, and the reserved stock never comes
     * back. This undoes those side effects so checkout is safe to retry.
     */
    public function releaseFailedCardOrder(Order $order): void
    {
        $order->loadMissing('items');

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                ProductVariant::query()
                    ->whereKey($item->product_variant_id)
                    ->increment('stock_quantity', $item->quantity);
            }

            $order->forceFill([
                'status' => OrderStatus::Cancelled,
                'payment_status' => PaymentStatus::Failed,
            ])->save();

            $this->releaseDiscountCode($order);
        });

        foreach ($order->items as $item) {
            try {
                $this->cart->add($item->product_variant_id, $item->quantity);
            } catch (\Throwable $e) {
                // Variant went out of stock or was deactivated in the few
                // seconds between order creation and the gateway failure —
                // nothing to restore for this line.
            }
        }
    }

    /**
     * Re-checks everything StoreCheckoutRequest already checked (unused,
     * first order only) — that earlier check and this one aren't atomic with
     * each other, so it can't be trusted alone; the actual claim right after
     * order creation is what's race-safe. Returns null silently rather than
     * throwing for "not found"/"already used" here, since a normal customer
     * can't hit this path without going through the FormRequest first — if
     * they do, quietly not discounting them is safer than blocking checkout.
     */
    private function resolveDiscountCode(?string $rawCode, ?User $user, ?string $email): ?DiscountCode
    {
        $code = trim((string) $rawCode);

        if ($code === '') {
            return null;
        }

        $discountCode = DiscountCode::query()->where('code', strtoupper($code))->first();

        if ($discountCode === null || $discountCode->isUsed()) {
            return null;
        }

        $hasPriorOrder = Order::query()
            ->where('status', '!=', OrderStatus::Cancelled)
            ->where(function ($query) use ($user, $email): void {
                if ($user !== null) {
                    $query->where('user_id', $user->id);
                }

                if ($email) {
                    $query->orWhere('guest_email', $email);
                }
            })
            ->exists();

        return $hasPriorOrder ? null : $discountCode;
    }

    /**
     * Mirrors the stock-restore above — an order that never ends up paid
     * shouldn't have permanently spent the customer's one discount code
     * attempt. Only releases the code if this specific order is the one
     * holding it, so a stale/incorrect discount_code_id can never free a
     * code some other order legitimately still owns.
     */
    private function releaseDiscountCode(Order $order): void
    {
        if ($order->discount_code_id === null) {
            return;
        }

        DiscountCode::query()
            ->whereKey($order->discount_code_id)
            ->where('used_by_order_id', $order->id)
            ->update(['used_at' => null, 'used_by_order_id' => null]);
    }

    private function shippingAmountForCountry(string $countryCode, string $subtotal): string
    {
        $code = strtoupper($countryCode);
        $countries = config('store.shipping.countries', []);

        if (! isset($countries[$code])) {
            throw new \InvalidArgumentException(__('Invalid shipping country.'));
        }

        if ($this->qualifiesForFreeShipping($subtotal)) {
            return '0.00';
        }

        return (string) $countries[$code]['amount'];
    }

    /**
     * Strictly over the threshold — an order of exactly the threshold amount
     * still pays normal shipping.
     */
    private function qualifiesForFreeShipping(string $subtotal): bool
    {
        return bccomp($subtotal, (string) config('store.shipping.free_over', '100.00'), 2) > 0;
    }
}
