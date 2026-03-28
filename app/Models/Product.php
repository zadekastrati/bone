<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images()->first();
    }

    /** @return list<array{name: string, hex: ?string}> */
    public function availableColors(): array
    {
        return $this->variants()
            ->orderBy('color')
            ->get()
            ->unique(fn (ProductVariant $v) => $v->color)
            ->values()
            ->map(fn (ProductVariant $v) => [
                'name' => $v->color,
                'hex' => $v->color_hex,
            ])
            ->all();
    }

    /** @return list<string> */
    public function availableSizesForColor(string $color): array
    {
        return $this->variants()
            ->where('color', $color)
            ->orderBy('size')
            ->pluck('size')
            ->unique()
            ->values()
            ->all();
    }
}
