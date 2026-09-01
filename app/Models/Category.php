<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'image_path',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function activeProducts(): HasMany
    {
        return $this->products()->where('is_active', true)->whereNull('deleted_at');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function imageUrl(): ?string
    {
        return $this->image_path !== null
            ? Storage::disk('public')->url($this->image_path)
            : null;
    }

    /**
     * Resized JPEG (max 1600px edge) instead of the raw camera-original
     * upload (routinely 2-4MB+ each) — for card-sized displays like the
     * homepage "Shop by category" grid, where every category's image paints
     * at once. Mirrors ProductImage::gridUrl().
     */
    public function gridImageUrl(): ?string
    {
        if ($this->image_path === null) {
            return null;
        }

        $ext = strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpeg', 'jpg', 'png', 'webp'], true)) {
            return $this->imageUrl();
        }

        return route('media.category-images.show', [$this, 'grid']);
    }
}
