<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    private const DEMO_IMAGE_STORAGE_PATH = 'products/demo-shared.avif';

    public function run(): void
    {
        $this->seedSharedImage();

        ProductImage::query()->delete();
        ProductVariant::query()->delete();
        Product::query()->forceDelete();
        Category::query()->delete();

        $payload = $this->inventoryPayload();

        $categoryModels = [];
        foreach ($payload['categories'] as $categoryRow) {
            $categoryModels[$categoryRow['slug']] = Category::query()->create([
                'name' => $categoryRow['name'],
                'slug' => $categoryRow['slug'],
                'description' => $categoryRow['description'],
                'sort_order' => $categoryRow['sort_order'],
            ]);
        }

        foreach ($payload['products'] as $productRow) {
            $slug = Str::slug($productRow['name']);
            if (Product::query()->where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }

            $product = Product::query()->create([
                'category_id' => $categoryModels[$productRow['categorySlug']]->id,
                'name' => $productRow['name'],
                'slug' => $slug,
                'description' => $productRow['description'],
                'price' => $productRow['price'],
                'is_active' => true,
            ]);

            if (Storage::disk('public')->exists(self::DEMO_IMAGE_STORAGE_PATH)) {
                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => self::DEMO_IMAGE_STORAGE_PATH,
                    'sort_order' => 0,
                ]);
            }

            $groupedVariants = [];
            foreach ($productRow['variants'] as $variantRow) {
                $variantKey = $variantRow['color'].'|'.$variantRow['size'];
                if (! isset($groupedVariants[$variantKey])) {
                    $groupedVariants[$variantKey] = $variantRow;

                    continue;
                }

                $groupedVariants[$variantKey]['stock_quantity'] += (int) $variantRow['stock_quantity'];
                if ($groupedVariants[$variantKey]['sku'] === null || $groupedVariants[$variantKey]['sku'] === '') {
                    $groupedVariants[$variantKey]['sku'] = $variantRow['sku'];
                }
            }

            foreach ($groupedVariants as $variantRow) {
                ProductVariant::query()->create([
                    'product_id' => $product->id,
                    'color' => $variantRow['color'],
                    'color_hex' => $variantRow['color_hex'],
                    'size' => $variantRow['size'],
                    'sku' => $variantRow['sku'],
                    'stock_quantity' => $variantRow['stock_quantity'],
                ]);
            }
        }
    }

    private function seedSharedImage(): void
    {
        $source = public_path('build/assets/images/1-1.jpeg');
        if (! is_readable($source)) {
            $this->command?->warn('Demo image not found at public/build/assets/images/1-1.jpeg — add the file or run storage:link after placing an image.');

            return;
        }

        Storage::disk('public')->put(
            self::DEMO_IMAGE_STORAGE_PATH,
            (string) file_get_contents($source)
        );
    }

    /**
     * @return array{categories: list<array{name: string, slug: string, description: string, sort_order: int}>, products: list<array{categorySlug: string, name: string, description: string, price: float, variants: list<array{color: string, color_hex: string, size: string, sku: string, stock_quantity: int}>}>}
     */
    private function inventoryPayload(): array
    {
        $path = database_path('seeders/data/bone_inventory.json');
        $payload = json_decode((string) file_get_contents($path), true);

        if (! is_array($payload) || ! isset($payload['categories'], $payload['products'])) {
            throw new \RuntimeException('Inventory payload is missing or malformed.');
        }

        $categoryMap = [];
        foreach ($payload['categories'] as $category) {
            $categoryMap[$category['name']] = $category['slug'];
        }

        $products = [];
        foreach ($payload['products'] as $product) {
            $products[] = [
                'categorySlug' => $categoryMap[$product['category']] ?? Str::slug($product['category']),
                'name' => $product['name'],
                'description' => $product['description'] ?? '',
                'price' => (float) $product['price'],
                'variants' => $product['variants'],
            ];
        }

        return [
            'categories' => $payload['categories'],
            'products' => $products,
        ];
    }
}
