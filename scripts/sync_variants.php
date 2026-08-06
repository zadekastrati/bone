<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function normalizeColorCode(string $color): string
{
    $normalized = preg_replace('/[^A-Za-z0-9]+/', ' ', trim($color));
    $words = array_values(array_filter(preg_split('/\s+/', strtoupper($normalized))));

    if (count($words) === 0) {
        return '';
    }

    if (count($words) === 1) {
        return substr($words[0], 0, 3);
    }

    if (count($words) === 2) {
        return substr($words[0], 0, 2) . substr($words[1], 0, 1);
    }

    return substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
}

$payloadPath = __DIR__ . '/../database/seeders/data/bone_inventory.json';
$payload = json_decode((string) file_get_contents($payloadPath), true);
if (!is_array($payload) || !isset($payload['products'])) {
    fwrite(STDERR, "Invalid seed payload\n");
    exit(1);
}

$service = new App\Services\ProductVariantSyncService();

foreach ($payload['products'] as $productRow) {
    if (!isset($productRow['name'], $productRow['variants']) || !is_array($productRow['variants'])) {
        continue;
    }

    $product = App\Models\Product::where('name', $productRow['name'])->first();
    if ($product === null) {
        fwrite(STDOUT, "SKIP missing product: {$productRow['name']}\n");
        continue;
    }

    $existingVariants = $product->variants->keyBy(function ($variant) {
        return $variant->color . '|' . $variant->size;
    });

    $rows = [];
    foreach ($productRow['variants'] as $variantRow) {
        if (!isset($variantRow['color'], $variantRow['size'], $variantRow['sku'], $variantRow['color_hex'])) {
            continue;
        }

        if ($variantRow['size'] !== 'M') {
            continue;
        }

        $colorCode = normalizeColorCode($variantRow['color']);
        $baseSku = $variantRow['sku'];
        $stockMap = ['S' => 2, 'M' => 4, 'L' => 2, 'XL' => 1];

        foreach ($stockMap as $size => $stockQuantity) {
            $sku = $size === 'M'
                ? $baseSku
                : sprintf('%s-%s-%s', $baseSku, $colorCode, $size);

            $row = [
                'color' => $variantRow['color'],
                'color_hex' => $variantRow['color_hex'],
                'size' => $size,
                'sku' => $sku,
                'stock_quantity' => $stockQuantity,
            ];

            $key = $variantRow['color'] . '|' . $size;
            if (isset($existingVariants[$key])) {
                $row['id'] = $existingVariants[$key]->id;
            }

            $rows[] = $row;
        }
    }

    if (count($rows) === 0) {
        fwrite(STDOUT, "SKIP no M variants for product: {$productRow['name']}\n");
        continue;
    }

    $service->sync($product, $rows);
    fwrite(STDOUT, "SYNCED product {$product->id}: {$product->name} (variants: " . count($rows) . ")\n");
}
