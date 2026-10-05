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

        Http::assertSentCount(2);
        Http::assertSent(fn (HttpClientRequest $request) => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '111'
            && str_contains($request['text'], 'Jane Doe'));
        Http::assertSent(fn (HttpClientRequest $request) => $request['chat_id'] === '222');
    }

    public function test_no_telegram_request_is_made_when_unconfigured(): void
    {
        config(['services.telegram.bot_token' => null, 'services.telegram.chat_ids' => []]);
        Http::fake();
        $this->addVariantToCart();

        $this->post(route('checkout.store'), $this->validCheckoutPayload());

        Http::assertNothingSent();
    }
}
