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

    public function isVideo(): bool
    {
        $ext = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));

        return in_array($ext, self::VIDEO_EXTENSIONS, true);
    }
}
