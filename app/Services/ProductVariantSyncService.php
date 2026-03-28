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
}
