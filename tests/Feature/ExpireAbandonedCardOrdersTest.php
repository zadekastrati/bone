<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExpireAbandonedCardOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(int $stock = 10): ProductVariant
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price' => '25.00',
            'is_active' => true,
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => $stock,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeOrderWithReservedStock(ProductVariant $variant, int $reservedQty, array $overrides = []): Order
    {
        // created_at isn't mass-assignable (not in Order::$fillable), so it
        // has to be backdated separately below via forceFill — passing it
        // through create() here would just be silently dropped.
        $createdAt = $overrides['created_at'] ?? null;
        unset($overrides['created_at']);

        $order = Order::create(array_merge([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => OrderStatus::Pending,
            'payment_method' => 'card',
            'payment_status' => PaymentStatus::Pending,
            'payment_gateway_order_id' => (string) random_int(100000, 999999),
            'payment_gateway_order_password' => 'secret-pass',
            'shipping_first_name' => 'Test',
            'shipping_last_name' => 'Buyer',
            'shipping_street' => 'Test street',
            'shipping_building' => '1',
            'shipping_phone' => '123456789',
            'shipping_city' => 'Pristina',
            'shipping_region' => 'Pristina',
            'shipping_postal_code' => '10000',
            'shipping_country' => 'XK',
            'subtotal' => '75.00',
            'shipping_amount' => '0.00',
            'total' => '75.00',
        ], $overrides));

        if ($createdAt !== null) {
            $order->forceFill(['created_at' => $createdAt])->save();
        }

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_name' => 'Test Product',
            'color' => $variant->color,
            'size' => $variant->size,
            'sku' => $variant->sku,
            'quantity' => $reservedQty,
            'unit_price' => '25.00',
            'line_total' => '75.00',
        ]);

        // Stock was already decremented at checkout time — simulate that
        // reservation directly rather than going through CheckoutService.
        $variant->decrement('stock_quantity', $reservedQty);

        return $order;
    }

    /**
     * Shape matches a real Quipu order-details response (see
     * QuipuPaymentConfirmationTest) — this command now re-confirms with
     * Quipu before expiring anything, so every candidate order needs one of
     * these faked to reach the point being tested at all.
     */
    private function fakeDeclinedResponse(string $total = '75.00'): void
    {
        Http::fake(['*3dss2test.quipu.de*' => Http::response([
            'order' => [
                'status' => 'Declined',
                'amount' => (float) $total,
                'currency' => 'EUR',
            ],
        ], 200)]);
    }

    public function test_expires_abandoned_pending_card_order_and_restores_its_exact_reserved_stock(): void
    {
        $this->fakeDeclinedResponse();

        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
        ]);

        $this->assertSame(7, $variant->fresh()->stock_quantity);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
        $this->assertSame(10, $variant->fresh()->stock_quantity);
    }

    public function test_does_not_expire_a_pending_card_order_still_within_the_threshold(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(10),
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
        // Still within the threshold — Quipu must never even be asked.
        Http::assertNothingSent();
    }

    public function test_never_releases_stock_or_changes_status_for_an_already_paid_order(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
            'status' => OrderStatus::Confirmed,
            'payment_status' => PaymentStatus::Paid,
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Confirmed, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        // Still decremented — a paid order's stock must never be touched.
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    public function test_does_not_expire_non_card_payment_methods(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
            'payment_method' => 'cash_on_delivery',
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    public function test_expiring_an_order_releases_its_discount_code_back_to_unused(): void
    {
        $this->fakeDeclinedResponse();

        $code = DiscountCode::create([
            'code' => 'CORE-TESTEXP',
            'percent_off' => 30,
        ]);

        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
            'discount_code_id' => $code->id,
        ]);

        // Simulates the order having claimed the code at checkout time.
        $code->update(['used_at' => now(), 'used_by_order_id' => $order->id]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $code->refresh();
        $this->assertNull($code->used_at);
        $this->assertNull($code->used_by_order_id);
    }

    public function test_running_the_command_twice_never_restores_stock_more_than_once(): void
    {
        $this->fakeDeclinedResponse();

        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();
        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
        $this->assertSame(10, $variant->fresh()->stock_quantity);
    }

    /**
     * DD-93: the whole point of re-confirming with Quipu before expiring —
     * an order that was actually paid must never be cancelled just because
     * it sat past the timeout (e.g. the confirmation callback never fired).
     */
    public function test_does_not_expire_an_order_that_quipu_confirms_was_actually_paid(): void
    {
        Http::fake(['*3dss2test.quipu.de*' => Http::response([
            'order' => [
                'status' => 'FullyPaid',
                'amount' => 75.00,
                'currency' => 'EUR',
                'trans' => [['approvalCode' => '123456', 'regTime' => null]],
                'srcToken' => ['displayName' => '1111******2222', 'card' => ['brand' => 'Visa']],
            ],
        ], 200)]);

        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Paid, $order->payment_status);
        // Reserved stock stays reserved — the order is genuinely fulfilled.
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    /**
     * DD-93: if Quipu can't be reached right now (network hiccup, timeout),
     * the order must be left Pending for the next sweep to retry — never
     * expired on a guess, since that risks releasing the stock of an order
     * that was actually charged.
     */
    public function test_leaves_an_order_pending_when_quipu_cannot_be_reached(): void
    {
        Http::fake(['*3dss2test.quipu.de*' => Http::response(['error' => 'timeout'], 500)]);

        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'created_at' => now()->subMinutes(90),
        ]);

        $this->artisan('orders:expire-abandoned-card-payments')->assertSuccessful();

        $order->refresh();
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(PaymentStatus::Pending, $order->payment_status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }
}
