<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscountCodeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function addVariantToCart(int $quantity = 1, string $price = '100.00'): ProductVariant
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price' => $price,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        $this->withSession(['store_cart' => [$variant->id => $quantity]]);

        return $variant;
    }

    /** @return array<string, mixed> */
    private function validCheckoutPayload(): array
    {
        return [
            'shipping_first_name' => 'Jane',
            'shipping_last_name' => 'Doe',
            'shipping_phone' => '044123456',
            'guest_email' => 'jane@example.com',
            'shipping_street' => 'Mother Teresa Boulevard 12',
            'shipping_city' => 'Pristina',
            'shipping_country' => 'XK',
            'payment_method' => 'cash_on_delivery',
            'terms_accepted' => true,
        ];
    }

    private function makeCode(int $percent = 30, ?string $label = 'CORE Pilates'): DiscountCode
    {
        return DiscountCode::create([
            'code' => 'CORE-TEST'.uniqid(),
            'label' => $label,
            'percent_off' => $percent,
        ]);
    }

    public function test_valid_discount_code_takes_the_percent_off_the_subtotal_only_not_shipping(): void
    {
        $code = $this->makeCode(30);
        // Below the free-shipping threshold, so shipping is real and must
        // stay untouched by the discount.
        $this->addVariantToCart(1, '50.00');

        $payload = $this->validCheckoutPayload();
        $payload['discount_code'] = $code->code;

        $this->post(route('checkout.store'), $payload)->assertSessionDoesntHaveErrors();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('50.00', $order->subtotal);
        $this->assertSame('15.00', $order->discount_amount);
        $this->assertSame($code->id, $order->discount_code_id);
        $this->assertSame(bcadd(bcsub('50.00', '15.00', 2), $order->shipping_amount, 2), $order->total);
    }

    public function test_discount_code_is_case_insensitive_and_trims_whitespace(): void
    {
        $code = $this->makeCode(30);
        $this->addVariantToCart(1, '50.00');

        $payload = $this->validCheckoutPayload();
        $payload['discount_code'] = '  '.strtolower($code->code).'  ';

        $this->post(route('checkout.store'), $payload)->assertSessionDoesntHaveErrors();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame($code->id, $order->discount_code_id);
    }

    public function test_checkout_without_a_discount_code_is_unaffected(): void
    {
        $this->addVariantToCart(1, '50.00');

        $this->post(route('checkout.store'), $this->validCheckoutPayload())->assertSessionDoesntHaveErrors();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertNull($order->discount_code_id);
        $this->assertSame('0.00', $order->discount_amount);
    }

    public function test_discount_code_is_marked_used_after_a_successful_order_and_cannot_be_reused(): void
    {
        $code = $this->makeCode(30);
        $this->addVariantToCart(1, '50.00');

        $this->post(route('checkout.store'), array_merge($this->validCheckoutPayload(), [
            'discount_code' => $code->code,
        ]));

        $order = Order::query()->latest('id')->firstOrFail();
        $code->refresh();
        $this->assertNotNull($code->used_at);
        $this->assertSame($order->id, $code->used_by_order_id);

        // A second, different customer tries the exact same code.
        $this->addVariantToCart(1, '50.00');
        $payload = $this->validCheckoutPayload();
        $payload['guest_email'] = 'someone-else@example.com';
        $payload['discount_code'] = $code->code;

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('discount_code');

        $this->assertSame(1, Order::query()->count());
    }

    public function test_unknown_discount_code_is_rejected(): void
    {
        $this->addVariantToCart(1, '50.00');

        $payload = $this->validCheckoutPayload();
        $payload['discount_code'] = 'NOT-A-REAL-CODE';

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('discount_code');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_discount_code_is_rejected_when_the_guest_email_already_has_a_prior_order(): void
    {
        $code = $this->makeCode(30);

        // First (undiscounted) order for this email.
        $this->addVariantToCart(1, '50.00');
        $this->post(route('checkout.store'), $this->validCheckoutPayload());
        $this->assertSame(1, Order::query()->count());

        // Same email tries to use a discount code on a second order.
        $this->addVariantToCart(1, '50.00');
        $payload = $this->validCheckoutPayload();
        $payload['discount_code'] = $code->code;

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('discount_code');

        $this->assertSame(1, Order::query()->count());
        $this->assertNull($code->fresh()->used_at);
    }

    public function test_a_cancelled_prior_order_does_not_block_the_discount_code(): void
    {
        $code = $this->makeCode(30);

        Order::create([
            'guest_email' => 'jane@example.com',
            'status' => 'cancelled',
            'payment_method' => 'card',
            'payment_status' => 'failed',
            'shipping_first_name' => 'Jane',
            'shipping_last_name' => 'Doe',
            'shipping_street' => 'Test street',
            'shipping_building' => '1',
            'shipping_phone' => '044123456',
            'shipping_city' => 'Pristina',
            'shipping_postal_code' => '10000',
            'shipping_country' => 'XK',
            'subtotal' => '20.00',
            'shipping_amount' => '0.00',
            'total' => '20.00',
        ]);

        $this->addVariantToCart(1, '50.00');
        $payload = $this->validCheckoutPayload();
        $payload['discount_code'] = $code->code;

        $this->post(route('checkout.store'), $payload)->assertSessionDoesntHaveErrors();

        $order = Order::query()->where('status', '!=', 'cancelled')->latest('id')->firstOrFail();
        $this->assertSame($code->id, $order->discount_code_id);
    }

    public function test_discount_code_is_released_when_card_payment_gateway_fails(): void
    {
        config(['services.quipu.enabled' => true]);
        Http::fake([
            '*3dss2test.quipu.de*' => Http::response(['error' => 'bad request'], 400),
        ]);
        $code = $this->makeCode(30);
        $this->addVariantToCart(1, '50.00');

        $payload = $this->validCheckoutPayload();
        $payload['payment_method'] = 'card';
        $payload['discount_code'] = $code->code;

        $this->post(route('checkout.store'), $payload);

        $code->refresh();
        $this->assertNull($code->used_at);
        $this->assertNull($code->used_by_order_id);
    }
}
