<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $product_id
 * @property string $color
 * @property string|null $color_hex
 * @property string $size
 * @property string|null $sku
 * @property int $stock_quantity
 */
class ProductVariant extends Model
{
    /**
     * Canonical smallest-to-largest rank for known apparel sizes. "2XL"/"3XL"
     * are accepted as synonyms for "XXL"/"XXXL" since both spellings are
     * common in admin-entered data.
     *
     * @var array<string, int>
     */
    private const SIZE_ORDER = [
        'XXS' => 0,
        'XS' => 1,
        'S' => 2,
        'M' => 3,
        'L' => 4,
        'XL' => 5,
        'XXL' => 6,
        '2XL' => 6,
        'XXXL' => 7,
        '3XL' => 7,
        '4XL' => 8,
        '5XL' => 9,
    ];

    protected $fillable = [
        'product_id',
        'color',
        'color_hex',
        'size',
        'sku',
        'stock_quantity',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'stock_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Sortable key for a size string: known apparel sizes come first in
     * smallest-to-largest order, then numeric sizes (e.g. waist/shoe sizes)
     * in ascending order, then anything else alphabetically. Usable directly
     * with Collection::sortBy() since it's a plain comparable string.
     */
    public static function sizeSortKey(string $size): string
    {
        $normalized = strtoupper(trim($size));

        if (array_key_exists($normalized, self::SIZE_ORDER)) {
            return sprintf('0-%03d', self::SIZE_ORDER[$normalized]);
        }

        if (is_numeric($normalized)) {
            return sprintf('1-%012.2f', (float) $normalized);
        }

        return '2-'.$normalized;
    }
}
