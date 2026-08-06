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
}
