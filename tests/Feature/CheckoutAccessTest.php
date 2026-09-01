<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutAccessTest extends TestCase
{
    use RefreshDatabase;

    private function addVariantToCart(int $quantity = 1): ProductVariant
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
            'shipping_street' => 'Mother Teresa Boulevard 12',
            'shipping_city' => 'Pristina',
            'shipping_country' => 'XK',
            'payment_method' => 'cash_on_delivery',
        ];
    }

    public function test_guest_is_redirected_from_checkout_when_cart_is_empty(): void
    {
        $this->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_guest_can_view_checkout_page_with_items_in_cart(): void
    {
        $this->addVariantToCart();

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Place order');
    }

    public function test_unverified_user_is_redirected_from_checkout(): void
    {
        $user = User::factory()->unverified()->create();
        $this->addVariantToCart();

        $this->actingAs($user)
            ->get(route('checkout.create'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_guest_can_place_an_order_and_reach_the_guest_confirmation_page(): void
    {
        $this->addVariantToCart();

        $response = $this->post(route('checkout.store'), $this->validCheckoutPayload());

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertNull($order->user_id);
        $this->assertSame('Jane', $order->shipping_first_name);

        $response->assertRedirect();
        $this->assertStringContainsString('/order-confirmation/'.$order->order_number, $response->headers->get('Location'));

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_guest_confirmation_page_rejects_an_unsigned_url(): void
    {
        $this->addVariantToCart();
        $this->post(route('checkout.store'), $this->validCheckoutPayload());
        $order = Order::query()->latest('id')->firstOrFail();

        $this->get('/order-confirmation/'.$order->order_number)
            ->assertForbidden();
    }

    public function test_verified_logged_in_user_can_still_place_an_order(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->addVariantToCart();

        $response = $this->actingAs($user)->post(route('checkout.store'), $this->validCheckoutPayload());

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame($user->id, $order->user_id);

        $response->assertRedirect(route('orders.show', $order));
    }

    public function test_checkout_rejects_an_invalid_phone_number(): void
    {
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['shipping_phone'] = '123';

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('shipping_phone');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_checkout_rejects_a_phone_number_from_the_wrong_country(): void
    {
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        // A well-formed, valid US number — but the order ships to Kosovo (XK).
        $payload['shipping_phone'] = '+14155552671';

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('shipping_phone');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_checkout_accepts_valid_numbers_for_each_supported_country(): void
    {
        $cases = [
            'XK' => '044123456',
            'AL' => '069 234 5678',
            'MK' => '070123456',
        ];

        foreach ($cases as $country => $phone) {
            $this->addVariantToCart();

            $payload = $this->validCheckoutPayload();
            $payload['shipping_country'] = $country;
            $payload['shipping_phone'] = $phone;

            $this->post(route('checkout.store'), $payload)
                ->assertSessionDoesntHaveErrors('shipping_phone');
        }
    }

    public function test_repeated_guest_checkout_attempts_are_rate_limited(): void
    {
        $payload = $this->validCheckoutPayload();
        $payload['shipping_phone'] = 'not-a-number';

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('checkout.store'), $payload)->assertStatus(302);
        }

        $this->post(route('checkout.store'), $payload)->assertStatus(429);
    }
}
