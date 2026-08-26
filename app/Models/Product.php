<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $style_code
 * @property string $description
 * @property float|string $price
 * @property bool $is_active
 */
class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'style_code',
        'description',
        'price',
        'is_active',
        'training_tags',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'training_tags' => 'array',
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

    /**
     * Order line items across all of this product's variants — used to
     * rank products by purchase popularity (Product -> ProductVariant -> OrderItem).
     */
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            OrderItem::class,
            ProductVariant::class,
            'product_id',
            'product_variant_id',
        );
    }

    /**
     * Search active products by name — case-insensitive substring match first
     * (handles the common case cheaply, in SQL), falling back to a fuzzy,
     * typo-tolerant match in PHP only when the substring search finds
     * nothing. This is what lets a near-miss like "leggins" still find
     * "The Sculpt Leggings": that's a one-letter edit away from "leggings",
     * not a substring of it, so no LIKE pattern would ever match it.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query->whereRaw('1 = 0');
        }

        $escaped = addcslashes($term, '%_\\');
        $like = '%'.$escaped.'%';

        $matchedIds = (clone $query)->where('name', 'like', $like)->pluck('id');

        if ($matchedIds->isEmpty() && mb_strlen($term) >= 3) {
            $matchedIds = self::fuzzySearchIds($query, $term);
        }

        return $query->whereIn('id', $matchedIds);
    }

    /**
     * @return Collection<int, int>
     */
    private static function fuzzySearchIds(Builder $query, string $term): Collection
    {
        $termWords = array_values(array_filter(preg_split('/\s+/', mb_strtolower($term))));
        if ($termWords === []) {
            return collect();
        }

        return (clone $query)->pluck('name', 'id')
            ->filter(function (string $name) use ($termWords): bool {
                $nameWords = preg_split('/\s+/', mb_strtolower($name));

                foreach ($termWords as $termWord) {
                    $hasMatch = collect($nameWords)->contains(
                        fn (string $nameWord): bool => self::isFuzzyMatch($termWord, $nameWord)
                    );

                    if (! $hasMatch) {
                        return false;
                    }
                }

                return true;
            })
            ->keys();
    }

    /**
     * True if $a and $b are within a small edit distance of each other —
     * one typo (missing/extra/wrong letter) for most words, two for longer
     * ones, where a single edit is proportionally less significant.
     */
    private static function isFuzzyMatch(string $a, string $b): bool
    {
        $shorter = min(mb_strlen($a), mb_strlen($b));
        $maxDistance = $shorter <= 10 ? 1 : 2;

        if (abs(mb_strlen($a) - mb_strlen($b)) > $maxDistance) {
            return false;
        }

        return levenshtein($a, $b) <= $maxDistance;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function primaryImage(): ?ProductImage
    {
        return $this->images()->first();
    }

    public function thumbnailImage(): ?ProductImage
    {
        if ($this->relationLoaded('images')) {
            $marked = $this->images->firstWhere('is_thumbnail', true);
            if ($marked !== null && ! $marked->isVideo()) {
                return $marked;
            }

            return $this->images->first(fn (ProductImage $image): bool => ! $image->isVideo())
                ?? $this->images->first();
        }

        $marked = $this->images()->where('is_thumbnail', true)->first();
        if ($marked !== null && ! $marked->isVideo()) {
            return $marked;
        }

        return $this->images()->get()->first(fn (ProductImage $image): bool => ! $image->isVideo())
            ?? $this->images()->first();
    }

    /**
     * No variant has stock, or there are no variants (treat as unavailable).
     */
    public function isSoldOut(): bool
    {
        if (array_key_exists('variants_sum_stock_quantity', $this->getAttributes())) {
            return (int) ($this->getAttributes()['variants_sum_stock_quantity'] ?? 0) < 1;
        }

        if ($this->relationLoaded('variants')) {
            if ($this->variants->isEmpty()) {
                return true;
            }

            return $this->variants->every(fn (ProductVariant $v): bool => $v->stock_quantity < 1);
        }

        return ! $this->variants()->where('stock_quantity', '>', 0)->exists();
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

    /**
     * The color the storefront gallery should show before the shopper picks
     * one: "Black" if the product has it, otherwise the darkest available
     * color by hex luminance, otherwise just the first color alphabetically.
     * Null when the product has no colors at all.
     */
    public function defaultColor(): ?string
    {
        $colors = $this->availableColors();

        if ($colors === []) {
            return null;
        }

        $black = collect($colors)->first(fn (array $c): bool => mb_strtolower($c['name']) === 'black');
        if ($black !== null) {
            return $black['name'];
        }

        $withHex = collect($colors)->filter(fn (array $c): bool => $c['hex'] !== null);
        if ($withHex->isNotEmpty()) {
            return $withHex->sortBy(fn (array $c): float => self::hexLuminance($c['hex']))->first()['name'];
        }

        return $colors[0]['name'];
    }

    private static function hexLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            return 255.0;
        }

        [$r, $g, $b] = array_map(fn (string $h): int => hexdec($h), str_split($hex, 2));

        return 0.299 * $r + 0.587 * $g + 0.114 * $b;
    }

    /** @return list<string> */
    public function availableSizesForColor(string $color): array
    {
        return $this->variants()
            ->where('color', $color)
            ->pluck('size')
            ->unique()
            ->sortBy(fn (string $size) => ProductVariant::sizeSortKey($size))
            ->values()
            ->all();
    }

    /**
     * Images tagged for the given color. Falls back to the product's
     * unassigned (shared) images when that color has none of its own —
     * this keeps existing products, whose images have no color yet,
     * showing the same photos for every color as before.
     *
     * @return Collection<int, ProductImage>
     */
    public function imagesForColor(string $color): Collection
    {
        $images = $this->relationLoaded('images') ? $this->images : $this->images()->get();

        $colorImages = $images->where('color', $color)->values();

        return $colorImages->isNotEmpty() ? $colorImages : $images->whereNull('color')->values();
    }
}
