<?php

namespace App\Services;

use App\Support\ImageResizer;
use App\Support\VideoThumbnailer;
use Illuminate\Support\Facades\Storage;

class ImageVariantCache
{
    private const VIDEO_EXTENSIONS = ['mp4', 'webm', 'mov', 'ogg', 'm4v'];
    /**
     * Max dimension (longest edge, px) per named size.
     *
     * "thumb" (320px) is sized for small tiles — admin gallery grids, the
     * library picker, the product-page thumbnail strip — all rendered well
     * under 200px, where 320px is already sharp even on retina screens.
     * "grid" (1600px) is for the shop's product grid cards, which render
     * much larger (300-450px+ CSS width) — on a 2-3x retina screen (e.g.
     * iPhone) that needs 700-1200px+ of real source pixels, so it matches
     * "display"'s cap to stay just as sharp as the product page itself
     * instead of being visibly softer than it.
     */
    public const VARIANTS = [
        'thumb' => 320,
        'grid' => 1600,
        'display' => 1600,
    ];

    /**
     * Resolves a single cached variant, generating it only on a genuine
     * cache miss. Two-tier cache: "local" disk is checked first and is
     * near-instant (no network round trip), but is wiped on every Railway
     * redeploy/restart. "public" (R2) is checked next — durable across
     * redeploys, but every hit still costs a real R2 network round trip
     * (tens to hundreds of ms), so a hit there also gets written back to
     * local, making every request after the first one on this container
     * instant again instead of paying that R2 cost every single time.
     */
    public function resolve(string $sourcePath, string $cacheKey, int $maxDimension, string $sourceDisk = 'public'): ?string
    {
        $fast = Storage::disk('local');
        if ($fast->exists($cacheKey)) {
            return $fast->get($cacheKey);
        }

        $durable = Storage::disk('public');
        if ($durable->exists($cacheKey)) {
            $bytes = $durable->get($cacheKey);
            $fast->put($cacheKey, $bytes);

            return $bytes;
        }

        $disk = Storage::disk($sourceDisk);
        if (! $disk->exists($sourcePath)) {
            return null;
        }

        $original = $disk->get($sourcePath);
        $bytes = $this->isVideo($sourcePath)
            ? VideoThumbnailer::extractFrame($original, $maxDimension)
            : ImageResizer::resize($original, $maxDimension);

        if ($bytes === null) {
            return null;
        }

        $fast->put($cacheKey, $bytes);
        $durable->put($cacheKey, $bytes);

        return $bytes;
    }

    private function isVideo(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::VIDEO_EXTENSIONS, true);
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
        $fast = Storage::disk('local');
        $durable = Storage::disk('public');

        $missing = array_filter(
            $cacheKeysByVariant,
            fn (int $maxDimension, string $cacheKey): bool => ! $fast->exists($cacheKey) && ! $durable->exists($cacheKey),
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
            $bytes = ImageResizer::resize($original, $maxDimension);
            $fast->put($cacheKey, $bytes);
            $durable->put($cacheKey, $bytes);
        }
    }
}
