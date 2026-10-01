<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartItemThumbnailColorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_shows_the_thumbnail_matching_the_variants_color_not_the_products_default(): void
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

        $blueVariant = ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Blue',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'color' => 'Red',
            'size' => 'M',
            'stock_quantity' => 10,
        ]);

        // Red is the product's "default" image (sort_order 0, so it's what
        // Product::images()->first() would return) — the cart must not show
        // this for a Blue line, even though it's first in upload order.
        $redImage = ProductImage::create([
            'product_id' => $product->id,
            'color' => 'Red',
            'path' => 'products/red.jpg',
            'sort_order' => 0,
        ]);

        $blueImage = ProductImage::create([
            'product_id' => $product->id,
            'color' => 'Blue',
            'path' => 'products/blue.jpg',
            'sort_order' => 1,
        ]);

        $this->withSession(['store_cart' => [$blueVariant->id => 1]]);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee($blueImage->thumbUrl(), false)
            ->assertDontSee($redImage->thumbUrl(), false);
    }
}
