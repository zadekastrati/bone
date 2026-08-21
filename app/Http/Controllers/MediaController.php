<?php

namespace App\Http\Controllers;

use App\Models\ProductImage;
use App\Services\ImageVariantCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    private const RESIZABLE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp', 'avif'];

    public function __construct(private readonly ImageVariantCache $variantCache)
    {
    }

    public function productImage(ProductImage $productImage, string $variant): Response|RedirectResponse
    {
        abort_unless(array_key_exists($variant, ImageVariantCache::VARIANTS), 404);

        $path = $productImage->path;
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        abort_unless(in_array($ext, self::RESIZABLE_EXTENSIONS, true), 404);

        if (! function_exists('imagecreatefromstring')) {
            return redirect(Storage::disk('public')->url($path));
        }

        $cacheKey = "thumbnails/product-image-{$productImage->id}-{$variant}.jpg";
        $bytes = $this->variantCache->resolve($path, $cacheKey, ImageVariantCache::VARIANTS[$variant]);

        abort_if($bytes === null, 404);

        return response($bytes, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
