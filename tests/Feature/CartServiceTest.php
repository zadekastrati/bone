<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
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

    /**
     * ProductVariant::product() is a plain belongsTo with no withTrashed(),
     * so its query already carries Product's SoftDeletingScope — the
     * moment a product is deleted (soft or force), $variant->product comes
     * back null rather than a trashed model, even though a variant can
     * still be sitting in a customer's cart session. Viewing the cart used
     * to crash with "Attempt to read property is_active on null" instead
     * of just quietly dropping the unavailable line.
     */
    public function test_viewing_cart_does_not_crash_when_a_line_items_product_was_deleted(): void
    {
        $variant = $this->addVariantToCart();
        $variant->product->delete();

        $this->get(route('cart.index'))->assertOk();
    }

    public function test_updating_cart_quantity_does_not_crash_when_the_products_deleted(): void
    {
        $variant = $this->addVariantToCart();
        $variant->product->delete();

        $this->patch(route('cart.update', $variant), ['quantity' => 2])
            ->assertRedirect(route('cart.index'));
    }
}
