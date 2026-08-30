<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ProductVariantSyncService
{
    /**
     * @param  list<array{id?: int|null, color: string, size: string, color_hex?: ?string, sku?: ?string, stock_quantity: int}>  $rows
     * @return array<string, string> old color => new color, for colors that
     *                                were cleanly renamed this sync (see
     *                                resolveCleanRenames). The caller uses
     *                                this to keep product_images.color and
     *                                this same request's image upload/attach
     *                                sections — both computed from the color
     *                                names that existed before this sync ran
     *                                — pointed at the right color instead of
     *                                silently going stale.
     */
    public function sync(Product $product, array $rows): array
    {
        return DB::transaction(function () use ($product, $rows): array {
            assert(isset($product->id));
            $keepIds = [];
            $colorChanges = [];

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
                        $oldColor = $variant->color;
                        $variant->update($payload);
                        $keepIds[] = $variant->id;

                        if ($oldColor !== $payload['color']) {
                            $colorChanges[] = ['from' => $oldColor, 'to' => $payload['color']];
                        }

                        continue;
                    }
                }

                $created = $product->variants()->create($payload);
                $keepIds[] = $created->id;
            }

            $product->variants()->whereNotIn('id', $keepIds)->delete();

            return $this->resolveCleanRenames($product, $colorChanges);
        });
    }

    /**
     * Of the colors that changed on existing rows this sync, only the ones
     * renamed *cleanly* are safe to treat as a rename: every row that had
     * the old color now consistently has the same new color, and no
     * variant is left with the old color at all. A product's colors aren't
     * a single field — they're whatever distinct "color" text repeats
     * across its size rows — so if just one row of several sharing a color
     * got hand-edited (a typo fixed on one size but not the others, say),
     * that's not a rename of the color, and treating it as one would
     * silently drag every other row's photos along with it.
     *
     * @param  list<array{from: string, to: string}>  $colorChanges
     * @return array<string, string>
     */
    private function resolveCleanRenames(Product $product, array $colorChanges): array
    {
        if ($colorChanges === []) {
            return [];
        }

        $newColorsByOld = [];
        foreach ($colorChanges as $change) {
            $newColorsByOld[$change['from']][] = $change['to'];
        }

        $currentColors = $product->variants()->pluck('color')->unique()->all();

        $renames = [];
        foreach ($newColorsByOld as $oldColor => $newColors) {
            $newColors = array_unique($newColors);
            if (count($newColors) !== 1 || in_array($oldColor, $currentColors, true)) {
                continue;
            }

            $renames[$oldColor] = $newColors[0];
        }

        return $renames;
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
