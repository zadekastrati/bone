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
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * DD-94: cancelling an order from the admin panel never released its
 * reserved stock — only the automatic paths (declined/abandoned card
 * payments) did. An admin manually cancelling any order silently left the
 * stock count wrong forever unless someone remembered to fix it by hand.
 */
class AdminOrderCancellationStockTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function makeOrderWithReservedStock(ProductVariant $variant, int $reservedQty, array $overrides = []): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'ORD-'.uniqid(),
            'guest_email' => 'buyer@example.com',
            'status' => OrderStatus::Pending,
            'payment_method' => 'bank_transfer',
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

        $variant->decrement('stock_quantity', $reservedQty);

        return $order;
    }

    private function makeVariant(int $stock = 10): ProductVariant
    {
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category-'.uniqid()]);
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

    public function test_cancelling_a_pending_order_from_admin_restores_its_reserved_stock(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3);
        $this->assertSame(7, $variant->fresh()->stock_quantity);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(10, $variant->fresh()->stock_quantity);
    }

    public function test_cancelling_a_confirmed_order_from_admin_restores_its_reserved_stock(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 2, overrides: [
            'status' => OrderStatus::Confirmed,
        ]);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $this->assertSame(10, $variant->fresh()->stock_quantity);
    }

    public function test_cancelling_an_order_releases_its_discount_code(): void
    {
        $code = DiscountCode::create(['code' => 'CORE-TESTADMIN', 'percent_off' => 30]);
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 1, overrides: [
            'discount_code_id' => $code->id,
        ]);
        $code->update(['used_at' => now(), 'used_by_order_id' => $order->id]);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $code->refresh();
        $this->assertNull($code->used_at);
        $this->assertNull($code->used_by_order_id);
    }

    /**
     * The whole point: once an order has actually shipped, the physical
     * item is out of the warehouse. Cancelling it afterwards (e.g. logging
     * a return) must never auto-increment stock that isn't really back yet.
     */
    public function test_cancelling_an_already_shipped_order_does_not_touch_stock(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'status' => OrderStatus::Shipped,
            'shipped_at' => now(),
        ]);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        // Still decremented — the item physically left, so nothing to restore.
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    public function test_cancelling_an_already_delivered_order_does_not_touch_stock(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'status' => OrderStatus::Delivered,
        ]);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }

    /**
     * Cancelling an already-cancelled order (e.g. two admins acting on the
     * same stale page, or a double click) must never restore stock twice.
     */
    public function test_cancelling_an_already_cancelled_order_does_not_restore_stock_again(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3, overrides: [
            'status' => OrderStatus::Cancelled,
        ]);
        // Simulates a cancellation that already ran once and already
        // restored this stock — the helper above decrements on order
        // creation regardless of status, so put it back to represent that.
        $variant->increment('stock_quantity', 3);
        $this->assertSame(10, $variant->fresh()->stock_quantity);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'cancelled'])
            ->assertOk();

        $this->assertSame(10, $variant->fresh()->stock_quantity);
    }

    public function test_moving_a_pending_order_to_confirmed_does_not_touch_stock(): void
    {
        $variant = $this->makeVariant(stock: 10);
        $order = $this->makeOrderWithReservedStock($variant, reservedQty: 3);

        $this->actingAs($this->makeAdmin())
            ->patchJson(route('admin.orders.quickStatus', $order), ['status' => 'confirmed'])
            ->assertOk();

        $this->assertSame(OrderStatus::Confirmed, $order->fresh()->status);
        $this->assertSame(7, $variant->fresh()->stock_quantity);
    }
}
