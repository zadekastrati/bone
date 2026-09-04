<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Mail\OrderPlacedMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutAccessTest extends TestCase
{
    use RefreshDatabase;

    private function addVariantToCart(int $quantity = 1, string $price = '25.00'): ProductVariant
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

    public function test_guest_order_stores_email_and_sends_confirmation(): void
    {
        Mail::fake();
        $this->addVariantToCart();

        $this->post(route('checkout.store'), $this->validCheckoutPayload());

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('jane@example.com', $order->guest_email);
        $this->assertNull($order->user_id);

        // OrderPlacedMail implements ShouldQueueAfterCommit, so Mail::send()
        // dispatches it to the queue rather than sending it inline.
        Mail::assertQueued(OrderPlacedMail::class, function (OrderPlacedMail $mail) use ($order) {
            return $mail->hasTo('jane@example.com') && $mail->order->is($order);
        });
    }

    public function test_guest_checkout_requires_an_email(): void
    {
        Mail::fake();
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        unset($payload['guest_email']);

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('guest_email');

        $this->assertSame(0, Order::query()->count());
        Mail::assertNotQueued(OrderPlacedMail::class);
    }

    public function test_logged_in_checkout_does_not_require_the_guest_email_field(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        unset($payload['guest_email']);

        $response = $this->actingAs($user)->post(route('checkout.store'), $payload);

        $order = Order::query()->latest('id')->firstOrFail();
        $response->assertSessionDoesntHaveErrors('guest_email');
        $this->assertSame($user->id, $order->user_id);
        $this->assertNull($order->guest_email);

        Mail::assertQueued(OrderPlacedMail::class, function (OrderPlacedMail $mail) use ($user, $order) {
            return $mail->hasTo($user->email) && $mail->order->is($order);
        });
    }

    public function test_guest_checkout_rejects_a_malformed_email(): void
    {
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['guest_email'] = 'not-an-email';

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('guest_email');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_shipping_price_matches_configured_rate_per_country(): void
    {
        $rates = config('store.shipping.countries');
        $phones = ['XK' => '044123456', 'AL' => '069 234 5678', 'MK' => '070123456'];

        foreach ($phones as $country => $phone) {
            $this->addVariantToCart();

            $payload = $this->validCheckoutPayload();
            $payload['shipping_country'] = $country;
            $payload['shipping_phone'] = $phone;

            $this->post(route('checkout.store'), $payload)->assertSessionDoesntHaveErrors();

            $order = Order::query()->latest('id')->firstOrFail();
            $this->assertSame($rates[$country]['amount'], $order->shipping_amount);
        }
    }

    public function test_free_shipping_threshold_is_unchanged_by_guest_checkout(): void
    {
        // Subtotal strictly over the configured free-shipping threshold.
        $this->addVariantToCart(1, '150.00');

        $this->post(route('checkout.store'), $this->validCheckoutPayload());

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame('0.00', $order->shipping_amount);
        $this->assertSame('150.00', $order->total);
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

    public function test_card_payment_option_is_hidden_when_the_feature_flag_is_disabled(): void
    {
        config(['services.quipu.enabled' => false]);
        $this->addVariantToCart();

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertDontSee('Card (Visa/Mastercard)');
    }

    public function test_card_payment_option_is_shown_when_the_feature_flag_is_enabled(): void
    {
        config(['services.quipu.enabled' => true]);
        $this->addVariantToCart();

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('Card (Visa/Mastercard)');
    }

    public function test_checkout_rejects_card_payment_when_the_feature_flag_is_disabled(): void
    {
        config(['services.quipu.enabled' => false]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['payment_method'] = 'card';

        $this->post(route('checkout.store'), $payload)
            ->assertSessionHasErrors('payment_method');

        $this->assertSame(0, Order::query()->count());
    }

    public function test_card_payment_creates_a_pending_order_without_a_confirmation_email_and_redirects_to_the_gateway(): void
    {
        config(['services.quipu.enabled' => true]);
        Mail::fake();
        Http::fake([
            '*3dss2test.quipu.de*' => Http::response([
                'order' => [
                    'id' => 555,
                    'password' => 'secret-pass',
                    // Realistically bare — the gateway returns just the base
                    // card-entry page, with no id/password query string.
                    'hppUrl' => 'https://3dss2test.quipu.de/flex',
                    'status' => 'Preparing',
                ],
            ], 200),
        ]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['payment_method'] = 'card';

        $response = $this->post(route('checkout.store'), $payload);

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertTrue($order->payment_status === PaymentStatus::Pending);
        $this->assertSame('555', (string) $order->payment_gateway_order_id);
        $this->assertSame('secret-pass', $order->payment_gateway_order_password);

        $response->assertRedirect('https://3dss2test.quipu.de/flex?id=555&password=secret-pass');
        Mail::assertNotQueued(OrderPlacedMail::class);
    }

    public function test_card_payment_gateway_failure_does_not_crash_checkout(): void
    {
        config(['services.quipu.enabled' => true]);
        Http::fake([
            '*3dss2test.quipu.de*' => Http::response(['error' => 'bad request'], 400),
        ]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['payment_method'] = 'card';

        $response = $this->post(route('checkout.store'), $payload);

        $response->assertRedirect(route('cart.index'));
        // The order was already created before the gateway call failed —
        // it just stays pending/unlinked for an admin to follow up on.
        $this->assertSame(1, Order::query()->count());
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
