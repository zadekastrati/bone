<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpClientRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramOrderAlertTest extends TestCase
{
    use RefreshDatabase;

    private function addVariantToCart(): ProductVariant
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

        $this->withSession(['store_cart' => [$variant->id => 1]]);

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

    public function test_placing_an_order_sends_a_telegram_alert_to_every_configured_chat(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_ids' => ['111', '222'],
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->addVariantToCart();

        $this->post(route('checkout.store'), $this->validCheckoutPayload());

        $order = \App\Models\Order::query()->latest('id')->firstOrFail();

        Http::assertSentCount(2);
        Http::assertSent(function (HttpClientRequest $request) use ($order) {
            $text = $request['text'];

            return $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
                && $request['chat_id'] === '111'
                && $request['parse_mode'] === 'HTML'
                && str_contains($text, 'NEW ORDER')
                && str_contains($text, $order->order_number)
                && str_contains($text, 'Jane Doe')
                && str_contains($text, '044123456')
                && str_contains($text, 'jane@example.com')
                && str_contains($text, 'Mother Teresa Boulevard 12')
                && str_contains($text, 'Test Product (Black, M) ×1')
                && str_contains($text, 'Shipping: '.config('store.currency_symbol').number_format((float) $order->shipping_amount, 2))
                && str_contains($text, '€25.00')
                && str_contains($text, 'Cash on delivery');
        });
        Http::assertSent(fn (HttpClientRequest $request) => $request['chat_id'] === '222');
    }

    public function test_telegram_message_escapes_html_special_characters_in_customer_input(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_ids' => ['111'],
        ]);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['shipping_last_name'] = 'O\'Brien <script>';

        $this->post(route('checkout.store'), $payload);

        Http::assertSent(function (HttpClientRequest $request) {
            $text = $request['text'];

            return ! str_contains($text, '<script>')
                && str_contains($text, 'O&#039;Brien &lt;script&gt;');
        });
    }

    public function test_no_telegram_request_is_made_when_unconfigured(): void
    {
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_ids' => []]);
        Http::fake();
        $this->addVariantToCart();

        $this->post(route('checkout.store'), $this->validCheckoutPayload());

        Http::assertNothingSent();
    }

    /**
     * A card order isn't actually confirmed at placement — the customer
     * still has to complete payment on Quipu's hosted page, which they
     * might abandon. Alerting here would mean a phone buzzing for orders
     * that never go through; QuipuPaymentService::confirmPayment() sends
     * this alert instead, once payment actually succeeds.
     */
    public function test_card_order_does_not_send_a_telegram_alert_at_placement(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_ids' => ['111'],
            'services.quipu.enabled' => true,
        ]);
        Http::fake([
            '*3dss2test.quipu.de*' => Http::response([
                'order' => ['id' => 555, 'password' => 'secret-pass', 'hppUrl' => 'https://3dss2test.quipu.de/flex', 'status' => 'Preparing'],
            ], 200),
            'api.telegram.org/*' => Http::response(['ok' => true], 200),
        ]);
        $this->addVariantToCart();

        $payload = $this->validCheckoutPayload();
        $payload['payment_method'] = 'card';

        $this->post(route('checkout.store'), $payload);

        Http::assertNotSent(fn (HttpClientRequest $request) => str_contains($request->url(), 'api.telegram.org'));
    }
}
