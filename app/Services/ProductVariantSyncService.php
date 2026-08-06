<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantSyncService
{
    /**
     * @param  list<array{id?: int|null, color: string, size: string, color_hex?: ?string, sku?: ?string, stock_quantity: int}>  $rows
     */
    public function sync(Product $product, array $rows): void
    {
        DB::transaction(function () use ($product, $rows): void {
            assert(isset($product->id));
            $keepIds = [];
            foreach ($rows as $row) {
                $payload = [
                    'color' => $row['color'],
                    'size' => $row['size'],
                    'color_hex' => $row['color_hex'] ?? null,
                    'sku' => isset($row['sku']) && $row['sku'] !== '' && $row['sku'] !== null
                        ? $row['sku']
                        : null,
                    'stock_quantity' => max(0, (int) $row['stock_quantity']),
                ];

                if ($payload['sku'] === null) {
                    $payload['sku'] = $this->generateSku($product, $payload['color'], $payload['size']);
                }

                if (! empty($row['id'])) {
                    $variant = ProductVariant::query()
                        ->where('product_id', $product->id)
                        ->whereKey((int) $row['id'])
                        ->first();
                    if ($variant !== null) {
                        $variant->update($payload);
                        $keepIds[] = $variant->id;

                        continue;
                    }
                }

                $created = $product->variants()->create($payload);
                $keepIds[] = $created->id;
            }

            $product->variants()->whereNotIn('id', $keepIds)->delete();
        });
    }

    private function generateSku(Product $product, string $color, string $size): string
    {
        $base = $this->resolveBaseCode($product);
        $colorCode = $this->normalizeColorCode($color);
        $sizeCode = strtoupper(trim($size));

        $skuParts = array_filter([$base, $colorCode, $sizeCode]);

        return implode('-', $skuParts);
    }

    private function resolveBaseCode(Product $product): string
    {
        if ($product->style_code !== null && trim($product->style_code) !== '') {
            return strtoupper(trim($product->style_code));
        }

        $existingSku = $product->variants()
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->value('sku');

        if ($existingSku !== null && strpos($existingSku, '-') !== false) {
            $segments = explode('-', $existingSku);
            if (count($segments) > 2) {
                return strtoupper(implode('-', array_slice($segments, 0, -2)));
            }
        }

        return strtoupper(trim($product->slug));
    }

    private function normalizeColorCode(string $color): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9]+/', ' ', trim($color));
        $words = array_values(array_filter(preg_split('/\s+/', strtoupper($normalized))));

        if (count($words) === 1) {
            return substr($words[0], 0, 3);
        }

        if (count($words) === 2) {
            return substr($words[0], 0, 2).substr($words[1], 0, 1);
        }

        return substr($words[0], 0, 1).substr($words[1], 0, 1).substr($words[2], 0, 1);
    }
}
