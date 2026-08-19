<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'ogg', 'm4v'];

    protected $fillable = [
        'product_id',
        'color',
        'path',
        'sort_order',
        'is_thumbnail',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_thumbnail' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /**
     * Small resized JPEG (max 320px edge) for grid/thumbnail-strip display.
     * Falls back to the full R2 original for videos, which aren't resized.
     */
    public function thumbUrl(): string
    {
        return $this->isVideo() ? $this->url() : route('media.product-images.show', [$this, 'thumb']);
    }

    /**
     * Resized JPEG (max 1600px edge) for full-size display, instead of the
     * 25-30+ megapixel camera original.
     */
    public function displayUrl(): string
    {
        return $this->isVideo() ? $this->url() : route('media.product-images.show', [$this, 'display']);
    }

    /**
     * Resized JPEG (max 800px edge) for the shop's product grid cards, which
     * render much larger than the small tiles "thumb" is sized for.
     */
    public function gridUrl(): string
    {
        return $this->isVideo() ? $this->url() : route('media.product-images.show', [$this, 'grid']);
    }

    public function isVideo(): bool
    {
        return self::isVideoPath($this->path);
    }

    public static function isVideoPath(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, self::VIDEO_EXTENSIONS, true);
    }
}
