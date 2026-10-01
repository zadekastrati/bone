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
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InternalCronExpireAbandonedOrdersTest extends TestCase
{
    use RefreshDatabase;

    private function makeAbandonedCardOrder(): Order
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

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        $order = Order::create([
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
            'payment_gateway_order_id' => (string) random_int(100000, 999999),
            'payment_gateway_order_password' => 'secret-pass',
        ]);
        $order->forceFill(['created_at' => now()->subMinutes(90)])->save();

        OrderItem::create([
            'order_id' => $order->id,
            'product_variant_id' => $variant->id,
            'product_id' => $variant->product_id,
            'product_name' => 'Test Product',
            'color' => $variant->color,
            'size' => $variant->size,
            'sku' => $variant->sku,
            'quantity' => 3,
            'unit_price' => '25.00',
            'line_total' => '75.00',
        ]);
        $variant->decrement('stock_quantity', 3);

        return $order;
    }

    public function test_the_route_404s_when_no_secret_is_configured(): void
    {
        config(['services.internal_cron.secret' => null]);

        $order = $this->makeAbandonedCardOrder();

        $this->get('/internal/cron/expire-abandoned-orders')->assertNotFound();
        $this->get('/internal/cron/expire-abandoned-orders?secret=')->assertNotFound();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_the_route_404s_with_a_wrong_secret(): void
    {
        config(['services.internal_cron.secret' => 'the-real-secret']);

        $order = $this->makeAbandonedCardOrder();

        $this->get('/internal/cron/expire-abandoned-orders?secret=wrong')->assertNotFound();

        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_the_route_expires_abandoned_card_orders_with_the_correct_secret(): void
    {
        config(['services.internal_cron.secret' => 'the-real-secret']);

        // DD-93: the expiry command now re-confirms with Quipu before
        // cancelling anything, so this needs a faked decline to reach it.
        Http::fake(['*3dss2test.quipu.de*' => Http::response([
            'order' => ['status' => 'Declined', 'amount' => 75.00, 'currency' => 'EUR'],
        ], 200)]);

        $order = $this->makeAbandonedCardOrder();

        $this->get('/internal/cron/expire-abandoned-orders?secret=the-real-secret')->assertOk();

        $order->refresh();
        $this->assertSame(OrderStatus::Cancelled, $order->status);
        $this->assertSame(PaymentStatus::Failed, $order->payment_status);
    }
}
