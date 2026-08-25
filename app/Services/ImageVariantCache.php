<?php

namespace App\Services;

use App\Support\ImageResizer;
use Illuminate\Support\Facades\Storage;

class ImageVariantCache
{
    /**
     * Max dimension (longest edge, px) per named size.
     *
     * "thumb" (320px) is sized for small tiles — admin gallery grids, the
     * library picker, the product-page thumbnail strip — all rendered well
     * under 200px, where 320px is already sharp even on retina screens.
     * "grid" (800px) is for the shop's product grid cards, which render
     * much larger (300-450px+ CSS width); reusing "thumb" there upscaled a
     * 320px source into that space and looked visibly soft/blurry compared
     * to the old full-resolution originals.
     */
    public const VARIANTS = [
        'thumb' => 320,
        'grid' => 800,
        'display' => 1600,
    ];

    /**
     * Resolves a single cached variant, generating it only on a genuine
     * cache miss. Checks the cache disk FIRST and returns immediately on a
     * hit — every call to $sourceDisk->exists()/get() is a real R2 network
     * round trip (tens to hundreds of ms each), so touching R2 to serve a
     * file that's already cached was pure waste.
     *
     * Cached on the "public" disk (R2), not "local" — Railway's container
     * filesystem is ephemeral and wiped on every redeploy/restart, which was
     * silently forcing every image to regenerate from scratch (a fresh R2
     * fetch + GD resize) after every deploy instead of ever staying warm.
     */
    public function resolve(string $sourcePath, string $cacheKey, int $maxDimension, string $sourceDisk = 'public'): ?string
    {
        $cache = Storage::disk('public');

        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $disk = Storage::disk($sourceDisk);
        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $bytes = ImageResizer::resize($disk->get($sourcePath), $maxDimension);
        $cache->put($cacheKey, $bytes);

        return $bytes;
    }

    /**
     * Generates every variant in $cacheKeysByVariant that isn't already
     * cached, from a single fetch of $sourcePath — as opposed to resolving
     * each variant independently, which would re-fetch the same multi-MB
     * original from R2 once per variant. Used to pre-warm the cache right
     * when a photo is attached to a product, so a customer's first view of
     * it is always a cache hit instead of paying the R2-fetch-and-resize
     * cost live in their request.
     *
     * @param  array<string, int>  $cacheKeysByVariant  cache key => max dimension
     */
    public function warm(string $sourcePath, array $cacheKeysByVariant, string $sourceDisk = 'public'): void
    {
        $cache = Storage::disk('public');

        $missing = array_filter(
            $cacheKeysByVariant,
            fn (int $maxDimension, string $cacheKey): bool => ! $cache->exists($cacheKey),
            ARRAY_FILTER_USE_BOTH
        );

        if ($missing === []) {
            return;
        }

        $disk = Storage::disk($sourceDisk);
        if (! $disk->exists($sourcePath)) {
            return;
        }

        $original = $disk->get($sourcePath);

        foreach ($missing as $cacheKey => $maxDimension) {
            $cache->put($cacheKey, ImageResizer::resize($original, $maxDimension));
        }
    }
}
