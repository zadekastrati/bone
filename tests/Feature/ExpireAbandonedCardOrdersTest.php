<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_expires_abandoned_pending_card_order_and_restores_its_exact_reserved_stock(): void
    {
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

    public function test_running_the_command_twice_never_restores_stock_more_than_once(): void
    {
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
}
