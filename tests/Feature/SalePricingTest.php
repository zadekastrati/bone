<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalePricingTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product-'.uniqid(),
            'price' => '25.00',
            'is_active' => true,
        ], $overrides));
    }

    public function test_is_on_sale_is_true_only_when_all_four_fields_are_set_and_within_the_date_window(): void
    {
        $onSale = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->subDay(),
            'sale_ends_at' => now()->addDay(),
        ]);
        $this->assertTrue($onSale->isOnSale());

        $notYetStarted = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->addDay(),
            'sale_ends_at' => now()->addDays(2),
        ]);
        $this->assertFalse($notYetStarted->isOnSale());

        $alreadyEnded = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->subDays(2),
            'sale_ends_at' => now()->subDay(),
        ]);
        $this->assertFalse($alreadyEnded->isOnSale());

        $noSaleFieldsAtAll = $this->makeProduct();
        $this->assertFalse($noSaleFieldsAtAll->isOnSale());
    }

    public function test_shop_grid_shows_the_sale_badge_and_strikethrough_price_only_while_on_sale(): void
    {
        $onSale = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->subDay(),
            'sale_ends_at' => now()->addDay(),
        ]);

        $this->get(route('shop.category', $onSale->category))
            ->assertOk()
            ->assertSee('-30%')
            ->assertSee('25.00') // struck-through original price still visible
            ->assertSee('17.50'); // the actual sale price

        $notOnSale = $this->makeProduct();

        $this->get(route('shop.category', $notOnSale->category))
            ->assertOk()
            ->assertDontSee('-30%');
    }

    public function test_checkout_order_summary_shows_the_strikethrough_original_price_and_discount(): void
    {
        $product = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->subDay(),
            'sale_ends_at' => now()->addDay(),
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        $this->withSession(['store_cart' => [$variant->id => 2]]);

        $this->get(route('checkout.create'))
            ->assertOk()
            ->assertSee('-30%')
            ->assertSee('50.00') // original_price (25.00) x 2, struck through
            ->assertSee('35.00'); // line_total: price (17.50) x 2
    }

    /**
     * The part that actually matters for real money: a product mid-sale
     * (original_price/discount_percent/dates all set) must still charge
     * exactly its `price` field at checkout — nothing about the new sale
     * columns should ever enter the amount actually billed.
     */
    public function test_checkout_charges_the_products_price_field_directly_regardless_of_sale_fields(): void
    {
        $product = $this->makeProduct([
            'price' => '17.50',
            'original_price' => '25.00',
            'discount_percent' => 30,
            'sale_starts_at' => now()->subDay(),
            'sale_ends_at' => now()->addDay(),
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Black',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        $this->withSession(['store_cart' => [$variant->id => 1]]);

        $this->post(route('checkout.store'), [
            'shipping_first_name' => 'Jane',
            'shipping_last_name' => 'Doe',
            'shipping_phone' => '044123456',
            'guest_email' => 'jane@example.com',
            'shipping_street' => 'Mother Teresa Boulevard 12',
            'shipping_city' => 'Pristina',
            'shipping_country' => 'XK',
            'payment_method' => 'cash_on_delivery',
            'terms_accepted' => true,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();
        $item = $order->items()->firstOrFail();

        $this->assertSame('17.50', (string) $item->unit_price);
        $this->assertSame('17.50', (string) $item->line_total);
    }
}
