<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'product_id',
        'product_name',
        'color',
        'size',
        'sku',
        'quantity',
        'unit_price',
        'line_total',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Direct link to the product, independent of product_variant_id — that
     * FK goes null the moment the exact ordered variant is later deleted
     * (e.g. the product's variants get edited), even though the product
     * itself, and its images, are untouched. This is what actually stays
     * resolvable for as long as the product exists, so it's what the order
     * thumbnail should be read from.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
