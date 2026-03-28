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

        $categories = $this->createCategories();
        $counts = $this->randomCountsPerCategory(count($categories), 50);

        $productIndex = 0;
        $skuSeq = 0;

        foreach ($categories as $i => $category) {
            $templates = self::productNameTemplates()[$category['slug']] ?? self::fallbackProductNames();
            for ($n = 0; $n < $counts[$i]; $n++) {
                $productIndex++;
                $baseName = $templates[$n % count($templates)];
                $name = $baseName.' '.$productIndex;
                $slug = Str::slug($baseName).'-'.$productIndex;
                if (Product::query()->where('slug', $slug)->exists()) {
                    $slug .= '-'.Str::lower(Str::random(4));
                }

                $price = round(random_int(2400, 14800) / 100, 2);

                $product = Product::query()->create([
                    'category_id' => $category['model']->id,
                    'name' => $name,
                    'slug' => $slug,
                    'description' => self::randomDescription(),
                    'price' => $price,
                    'is_active' => true,
                ]);

                ProductImage::query()->create([
                    'product_id' => $product->id,
                    'path' => self::DEMO_IMAGE_STORAGE_PATH,
                    'sort_order' => 0,
                ]);

                foreach ($this->uniqueVariantRows($this->randomVariantMatrix()) as $row) {
                    $skuSeq++;
                    ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'color' => $row['color'],
                        'color_hex' => $row['color_hex'],
                        'size' => $row['size'],
                        'sku' => sprintf('BN-%06d', $skuSeq),
                        'stock_quantity' => random_int(4, 32),
                    ]);
                }
            }
        }
    }

    private function seedSharedImage(): void
    {
        $source = public_path('build/assets/images/images.avif');
        if (! is_readable($source)) {
            $this->command?->warn('Demo image not found at public/build/assets/images/images.avif — add the file or run storage:link after placing an image.');

            return;
        }

        Storage::disk('public')->put(
            self::DEMO_IMAGE_STORAGE_PATH,
            (string) file_get_contents($source)
        );
    }

    /**
     * @return list<array{model: Category, slug: string}>
     */
    private function createCategories(): array
    {
        $rows = [
            ['name' => 'Leggings', 'slug' => 'leggings', 'description' => 'High-rise holds, squat-proof fabrics, full length to 7/8.', 'sort_order' => 1],
            ['name' => 'Sports bras', 'slug' => 'sports-bras', 'description' => 'Low to high impact — racerback, longline, and strappy.', 'sort_order' => 2],
            ['name' => 'Tops & tanks', 'slug' => 'tops-tanks', 'description' => 'Crops, tanks, and tees for training and rest days.', 'sort_order' => 3],
            ['name' => 'Hoodies & layers', 'slug' => 'hoodies-layers', 'description' => 'Fleece, zip-ups, and lightweight cover-ups.', 'sort_order' => 4],
            ['name' => 'Shorts', 'slug' => 'shorts', 'description' => 'Bike shorts, training shorts, and relaxed fits.', 'sort_order' => 5],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Bags, caps, and studio essentials.', 'sort_order' => 6],
            ['name' => 'Socks & basics', 'slug' => 'socks-basics', 'description' => 'Grip socks, crews, and everyday base layers.', 'sort_order' => 7],
        ];

        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'model' => Category::query()->create($row),
                'slug' => $row['slug'],
            ];
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private function randomCountsPerCategory(int $categoryCount, int $totalProducts): array
    {
        $counts = array_fill(0, $categoryCount, 0);
        for ($i = 0; $i < $totalProducts; $i++) {
            $counts[random_int(0, $categoryCount - 1)]++;
        }

        return $counts;
    }

    /**
     * @return array<string, list<string>>
     */
    private static function productNameTemplates(): array
    {
        return [
            'leggings' => [
                'Core Compression Legging', 'Sculpt Seamless Tight', 'Velocity Pocket Legging',
                'Altitude High-Rise', 'Studio Matte Full Length', 'Pulse 7/8 Legging',
            ],
            'sports-bras' => [
                'Pulse Medium-Impact Bra', 'Lockdown High-Support Bra', 'Airlight Strappy Bra',
                'Contour Longline Bra', 'Sprint Racer Bra', 'Balance Crop Bra',
            ],
            'tops-tanks' => [
                'Breathe Mesh Tank', 'Define Crop Tee', 'Flow Muscle Tank',
                'Edge Rib Crop', 'Relay Slim Tee', 'Zen Twist-Back Tank',
            ],
            'hoodies-layers' => [
                'Recover Zip Hoodie', 'Drift Oversized Crew', 'Altitude Windbreaker',
                'Softstart Quarter-Zip', 'Nightrun Packable Jacket', 'Rest Day Fleece',
            ],
            'shorts' => [
                'Tempo 5" Short', 'Spin Bike Short', 'Track Relaxed Short',
                'Core 3" Hot Short', 'Trail Hybrid Short', 'Lift Oversized Short',
            ],
            'accessories' => [
                'Studio Tote', 'Train Duffel', 'Essential Cap',
                'Grip Towel Set', 'Belt Bag', 'Foam Roller Sleeve',
            ],
            'socks-basics' => [
                'Grip Studio Sock', 'Crew Cushion Sock', 'No-Show Tab Sock',
                'Compression Calf Sleeve', 'Ribbed Ankle Sock', 'Training Liner 3-Pack',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    private static function fallbackProductNames(): array
    {
        return ['boné Essential Piece', 'Train Daily Staple', 'Studio Standard'];
    }

    private static function randomDescription(): string
    {
        $lines = [
            'Built for sweat sessions and easy days off. True-to-size block with stretch recovery.',
            'Matte finish, flat seams, and a stay-put waistband you can trust under load.',
            'Lightweight layers with breathable panels — warm-up through cool-down.',
            'Designed for movement: squat-proof where it matters, soft where it counts.',
            'Limited colour drops — grab your size before the run sells through.',
        ];

        return $lines[array_rand($lines)];
    }

    /**
     * @return list<array{color: string, color_hex: string, size: string}>
     */
    private function randomVariantMatrix(): array
    {
        $palette = [
            ['color' => 'Black', 'color_hex' => '#1a1a1a'],
            ['color' => 'Graphite', 'color_hex' => '#4a5568'],
            ['color' => 'Navy', 'color_hex' => '#1e3a5f'],
            ['color' => 'Rose', 'color_hex' => '#be7c7c'],
            ['color' => 'Sage', 'color_hex' => '#6b8f71'],
            ['color' => 'Sand', 'color_hex' => '#c4b59d'],
        ];
        $sizes = ['XS', 'S', 'M', 'L', 'XL'];

        shuffle($palette);
        $pickColors = array_slice($palette, 0, random_int(2, 4));
        $rows = [];
        foreach ($pickColors as $c) {
            shuffle($sizes);
            foreach (array_slice($sizes, 0, random_int(2, 4)) as $size) {
                $rows[] = [
                    'color' => $c['color'],
                    'color_hex' => $c['color_hex'],
                    'size' => $size,
                ];
            }
        }

        return $rows;
    }

    /**
     * @param  list<array{color: string, color_hex: string, size: string}>  $rows
     * @return list<array{color: string, color_hex: string, size: string}>
     */
    private function uniqueVariantRows(array $rows): array
    {
        $out = [];
        $seen = [];
        foreach ($rows as $row) {
            $key = $row['color'].'|'.$row['size'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $row;
        }

        return $out !== [] ? $out : [
            ['color' => 'Black', 'color_hex' => '#1a1a1a', 'size' => 'M'],
        ];
    }
}
