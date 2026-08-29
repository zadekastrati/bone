<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageVariantCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryController extends Controller
{
    private const MEDIA_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp', 'mp4', 'webm', 'mov', 'ogg', 'm4v'];

    private const THUMBNAIL_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp', 'mp4', 'webm', 'mov', 'ogg', 'm4v'];

    private const IMAGE_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp'];

    private const THUMBNAIL_MAX_DIMENSION = 400;

    /**
     * Files on the "public" disk that are never product media, so they should
     * never show up as "available" in the product picker even though nothing
     * in product_images/categories references them by path.
     */
    private const EXCLUDED_PATHS = [
        'Horizontal_1.mp4', // homepage hero banner (resources/views/welcome.blade.php)
    ];

    public function __construct(private readonly ImageVariantCache $variantCache)
    {
    }

    /**
     * List files sitting on the "public" disk (R2) for the admin "choose from
     * library" picker. Files already attached to a product or category stay
     * listed — the same R2 photo is meant to be reusable across multiple
     * products, so being in use elsewhere is not a reason to hide it.
     */
    public function index(): JsonResponse
    {
        $this->authorize('create', Product::class);

        $disk = Storage::disk('public');

        $excluded = self::EXCLUDED_PATHS;

        $files = collect($disk->allFiles())
            ->reject(fn (string $path): bool => in_array($path, $excluded, true) || str_starts_with($path, 'thumbnails/'))
            ->filter(function (string $path): bool {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return in_array($ext, self::MEDIA_EXTENSIONS, true);
            })
            ->map(fn (string $path): array => [
                'path' => $path,
                'url' => $disk->url($path),
                'is_video' => ProductImage::isVideoPath($path),
                'folder' => str_contains($path, '/') ? Str::beforeLast($path, '/') : null,
            ])
            ->values();

        return response()->json(['files' => $files]);
    }

    /**
     * Returns every cached thumbnail for a batch of paths in a single
     * response, as inline base64 data URIs, instead of one HTTP request per
     * tile.
     *
     * The picker previously fetched each tile's thumbnail from `thumbnail()`
     * below as it scrolled into view — correct, but each of those is a
     * separate round trip, and this app's dev environment (and to a lesser
     * extent, any request) pays a real fixed per-request cost on top of the
     * actual image bytes. Multiply that by "however many tiles fit on
     * screen" and opening a folder feels sluggish even though every
     * thumbnail is already cached and nothing is being resized live.
     * Returning a whole folder's worth of already-cached thumbnails in one
     * response cuts that down to one request no matter how many tiles it
     * contains, so the grid can render immediately once it arrives. Only
     * already-cached thumbnails are included — see the class docblock on
     * `thumbnail()` for the cache itself; nothing is resized on demand here,
     * a cache miss is just omitted and the tile falls back to `thumbnail()`.
     *
     * Takes the folder's file paths directly from the client — which already
     * has them from index() above — rather than re-deriving folder membership
     * with a fresh $disk->allFiles() call here. That call is a real R2
     * ListObjects round trip (well over a second on its own), and repeating
     * it on every folder open was pure waste on top of the thumbnail reads.
     */
    public function folderThumbnails(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $paths = collect($request->input('paths', []))
            ->filter(fn ($path): bool => is_string($path) && $path !== '')
            ->filter(function (string $path): bool {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return in_array($ext, self::THUMBNAIL_EXTENSIONS, true);
            })
            ->take(200)
            ->values();

        $cache = Storage::disk('local');
        $thumbnails = [];

        foreach ($paths as $path) {
            $bytes = $cache->get('thumbnails/'.sha1($path).'.jpg');

            if ($bytes !== null) {
                $thumbnails[$path] = 'data:image/jpeg;base64,'.base64_encode($bytes);
            }
        }

        return response()->json(['thumbnails' => $thumbnails]);
    }

    /**
     * Serves a small resized JPEG for a library file instead of the R2 original.
     * These photos are full camera originals (routinely 25-30+ megapixels) —
     * decoding several of those at once in a browser tab to show as ~150px grid
     * tiles is what was actually making the picker unusably slow, not just the
     * network transfer. Resized once here and cached on the "public" disk (R2,
     * under thumbnails/ — excluded from the library listing above) so every
     * request after the first for a given file is served instantly with no
     * R2 round-trip and no re-encoding, and survives redeploys/restarts
     * instead of regenerating from scratch every time (Railway's container
     * filesystem is wiped on every deploy).
     */
    public function thumbnail(Request $request): Response|RedirectResponse
    {
        $this->authorize('create', Product::class);

        $path = (string) $request->query('path', '');
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        abort_if($path === '' || ! in_array($ext, self::THUMBNAIL_EXTENSIONS, true), 404);

        $cacheKey = 'thumbnails/'.sha1($path).'.jpg';
        $isImage = in_array($ext, self::IMAGE_EXTENSIONS, true);
        $alreadyCached = Storage::disk('local')->exists($cacheKey) || Storage::disk('public')->exists($cacheKey);

        if ($isImage && ! function_exists('imagecreatefromstring') && ! $alreadyCached) {
            // No GD available and nothing cached yet — fall back to the
            // original rather than error, so the picker still works, just
            // without the speed-up.
            $disk = Storage::disk('public');
            abort_unless($disk->exists($path), 404);

            return redirect($disk->url($path));
        }

        $bytes = $this->variantCache->resolve($path, $cacheKey, self::THUMBNAIL_MAX_DIMENSION);

        if ($bytes === null) {
            // Video frame extraction can fail (missing ffmpeg, corrupt file,
            // unsupported codec) — fall back to the raw file so the tile at
            // least loads something instead of a permanently broken icon.
            if (! $isImage) {
                $disk = Storage::disk('public');
                abort_unless($disk->exists($path), 404);

                return redirect($disk->url($path));
            }

            abort(404);
        }

        return response($bytes, 200, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
