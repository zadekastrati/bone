<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedMail;
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
        private readonly CartService $cart
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
    public function placeOrder(User $user, array $data): Order
    {
        $lines = $this->cart->lines();
        if ($lines->isEmpty()) {
            throw new \InvalidArgumentException('Your cart is empty.');
        }

        $order = DB::transaction(function () use ($user, $data, $lines) {
            $subtotal = $this->cart->subtotal();
            $shipping = $this->shippingAmountForCountry($data['shipping_country']);

            $order = Order::create([
                'user_id' => $user->id,
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
                'shipping_amount' => $shipping,
                'total' => bcadd($subtotal, $shipping, 2),
                'customer_notes' => $data['customer_notes'],
            ]);

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
                    throw new \RuntimeException('A product in your cart is no longer available.');
                }

                $qty = $line['quantity'];

                if (! $variant->product->is_active || $variant->product->trashed()) {
                    throw new \RuntimeException('A product in your cart is no longer available.');
                }

                if (! $variant->isInStock($qty)) {
                    throw new \RuntimeException('Insufficient stock for '.$variant->product->name.' ('.$variant->color.' / '.$variant->size.').');
                }

                $unit = (string) $variant->product->price;
                $lineTotal = bcmul($unit, (string) $qty, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_variant_id' => $variant->id,
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

        try {
            Mail::to($user->email)->send(new OrderPlacedMail($order));
        } catch (\Throwable $e) {
            Log::error('Order confirmation email failed', [
                'order_id' => $order->id,
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);
        }

        return $order;
    }

    private function shippingAmountForCountry(string $countryCode): string
    {
        $code = strtoupper($countryCode);
        $countries = config('store.shipping.countries', []);

        if (! isset($countries[$code])) {
            throw new \InvalidArgumentException('Invalid shipping country.');
        }

        return (string) $countries[$code]['amount'];
    }
}
