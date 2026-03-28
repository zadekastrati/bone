<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderPaginationTestSeeder extends Seeder
{
    /**
     * Seeds sample orders for admin / account pagination testing.
     * 21 rows → with 20 per page you get two pages (≈20 as requested, plus one to trigger pagination).
     */
    private const COUNT = 21;

    public function run(): void
    {
        $user = User::query()->where('email', 'user@example.com')->first()
            ?? User::query()->where('role', '!=', 'admin')->first()
            ?? User::query()->first();

        if ($user === null) {
            $this->command?->error('No user found. Run php artisan migrate:fresh --seed first.');

            return;
        }

        $variant = ProductVariant::query()->with('product')->first();

        $statuses = OrderStatus::cases();

        DB::transaction(function () use ($user, $variant, $statuses): void {
            for ($i = 0; $i < self::COUNT; $i++) {
                $shipping = '2.50';
                $qty = null;
                $lineTotal = null;
                $product = null;

                if ($variant !== null) {
                    $product = $variant->product;
                    $unit = (string) $product->price;
                    $qty = random_int(1, 3);
                    $lineTotal = bcmul($unit, (string) $qty, 2);
                    $subtotal = $lineTotal;
                    $total = bcadd($lineTotal, $shipping, 2);
                } else {
                    $subtotal = number_format(random_int(1500, 12000) / 100, 2, '.', '');
                    $total = bcadd($subtotal, $shipping, 2);
                }

                $order = Order::query()->create([
                    'user_id' => $user->id,
                    'status' => $statuses[$i % count($statuses)],
                    'payment_method' => $i % 2 === 0 ? PaymentMethod::BankTransfer : PaymentMethod::CashOnDelivery,
                    'payment_status' => PaymentStatus::Pending,
                    'shipping_first_name' => 'Test',
                    'shipping_last_name' => 'Order '.($i + 1),
                    'shipping_phone' => '+38344'.str_pad((string) (120000 + $i), 6, '0', STR_PAD_LEFT),
                    'shipping_street' => 'Rruga Test',
                    'shipping_building' => (string) (($i % 5) + 1),
                    'shipping_city' => 'Prishtina',
                    'shipping_region' => null,
                    'shipping_postal_code' => '10000',
                    'shipping_country' => 'XK',
                    'shipping_delivery_notes' => null,
                    'subtotal' => $subtotal,
                    'shipping_amount' => $shipping,
                    'total' => $total,
                    'customer_notes' => null,
                    'admin_notes' => null,
                ]);

                $created = now()->subHours(self::COUNT - $i);
                $order->forceFill([
                    'created_at' => $created,
                    'updated_at' => $created,
                ])->saveQuietly();

                if ($variant !== null && $product !== null && $qty !== null && $lineTotal !== null) {
                    $unit = (string) $product->price;

                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $product->name,
                        'color' => $variant->color,
                        'size' => $variant->size,
                        'sku' => $variant->sku,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                        'line_total' => $lineTotal,
                    ]);
                }
            }
        });

        $this->command?->info('Created '.self::COUNT.' test orders for user: '.$user->email.'.');
    }
}
