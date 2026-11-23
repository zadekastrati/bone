<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductCreationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_with_variants_auto_generated_skus_and_an_image(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'New Collection',
            'slug' => 'new-collection-'.uniqid(),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Hoodie',
            'price' => '49.99',
            'is_active' => '1',
            'variants' => [
                ['color' => 'Black', 'size' => 'M', 'stock_quantity' => 10],
                ['color' => 'Black', 'size' => 'L', 'stock_quantity' => 5],
                ['color' => 'Olive Green', 'size' => 'M', 'stock_quantity' => 8],
            ],
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Test Hoodie')->firstOrFail();

        $this->assertSame(3, $product->variants()->count());
        $this->assertSame(1, $product->images()->count());
        $this->assertTrue($product->images()->first()->is_thumbnail);

        $skus = $product->variants()->pluck('sku', 'color')->all();
        $blackM = ProductVariant::where('product_id', $product->id)
            ->where('color', 'Black')->where('size', 'M')->first();
        $blackL = ProductVariant::where('product_id', $product->id)
            ->where('color', 'Black')->where('size', 'L')->first();
        $olive = ProductVariant::where('product_id', $product->id)
            ->where('color', 'Olive Green')->where('size', 'M')->first();

        // SKUs were auto-generated (blank on submission) and are non-empty, unique per row.
        $this->assertNotNull($blackM->sku);
        $this->assertNotNull($blackL->sku);
        $this->assertNotNull($olive->sku);
        $this->assertSame(3, collect([$blackM->sku, $blackL->sku, $olive->sku])->unique()->count());

        // Same color, different size => same color-code segment, different size segment.
        $this->assertNotSame($blackM->sku, $blackL->sku);
        $this->assertStringEndsWith('-M', $blackM->sku);
        $this->assertStringEndsWith('-L', $blackL->sku);
    }

    public function test_creating_a_second_product_never_collides_with_an_existing_sku(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'New Collection',
            'slug' => 'new-collection-'.uniqid(),
        ]);

        $first = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Classic Tee',
            'price' => '19.99',
            'variants' => [
                ['color' => 'Black', 'size' => 'M', 'stock_quantity' => 10],
            ],
        ]);
        $first->assertRedirect(route('admin.products.index'));

        $second = $this->actingAs($admin)->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Classic Tee V2',
            'price' => '21.99',
            'variants' => [
                ['color' => 'Black', 'size' => 'M', 'stock_quantity' => 10],
            ],
        ]);
        $second->assertRedirect(route('admin.products.index'));

        $product1 = Product::where('name', 'Classic Tee')->firstOrFail();
        $product2 = Product::where('name', 'Classic Tee V2')->firstOrFail();

        $sku1 = $product1->variants()->first()->sku;
        $sku2 = $product2->variants()->first()->sku;

        $this->assertNotNull($sku1);
        $this->assertNotNull($sku2);
        $this->assertNotSame($sku1, $sku2, 'Two different products must never receive the same auto-generated SKU.');
    }
}
