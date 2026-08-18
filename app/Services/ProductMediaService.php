<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductMediaService
{
    /**
     * @param  list<UploadedFile|null>  $files
     */
    public function storeUploads(Product $product, array $files, ?string $color = null): void
    {
        if ($files === []) {
            return;
        }

        $sortOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($files as $file) {
            if ($file === null || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('products', 'public');
            $product->images()->create([
                'color' => $color,
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
            $sortOrder++;
        }

        $this->ensureThumbnail($product);
    }

    /**
     * Attach files that already exist on the "public" disk (e.g. R2 objects uploaded
     * outside the app) to $product, without uploading or moving anything. Paths already
     * attached to any product, or missing from the disk, are silently skipped here — the
     * real guard is StoreProductRequest/UpdateProductRequest validation, which rejects
     * the whole submission with a clear error; this is just a defensive second check.
     *
     * @param  list<string>  $paths
     */
    public function attachExisting(Product $product, array $paths, ?string $color = null): void
    {
        $paths = array_values(array_unique(array_filter(
            $paths,
            fn ($path): bool => is_string($path) && $path !== ''
        )));

        if ($paths === []) {
            return;
        }

        $alreadyAttached = ProductImage::query()->whereIn('path', $paths)->pluck('path')->all();
        $paths = array_values(array_diff($paths, $alreadyAttached));

        if ($paths === []) {
            return;
        }

        $disk = Storage::disk('public');
        $sortOrder = (int) $product->images()->max('sort_order') + 1;

        foreach ($paths as $path) {
            if (! $disk->exists($path)) {
                continue;
            }

            $product->images()->create([
                'color' => $color,
                'path' => $path,
                'sort_order' => $sortOrder,
            ]);
            $sortOrder++;
        }

        $this->ensureThumbnail($product);
    }

    /**
     * @param  list<int|string>  $ids
     */
    public function deleteImages(Product $product, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $images = ProductImage::query()
            ->where('product_id', $product->id)
            ->whereIn('id', $ids)
            ->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $this->ensureThumbnail($product);
    }

    /**
     * Persist a new drag-and-drop order for one color's images. Only images
     * that both belong to the product AND match $color are touched, so this
     * can never reassign an image to a different color or move it out of
     * its section — it only reorders within it.
     *
     * @param  list<int|string>  $orderedIds
     */
    public function reorderImages(Product $product, ?string $color, array $orderedIds): void
    {
        $images = ProductImage::query()
            ->where('product_id', $product->id)
            ->where('color', $color)
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy(fn (ProductImage $image): string => (string) $image->id);

        if ($images->isEmpty()) {
            return;
        }

        $baseSortOrder = $images->min('sort_order');

        $position = 0;
        foreach ($orderedIds as $id) {
            $image = $images->get((string) $id);
            if ($image === null) {
                continue;
            }

            $image->update(['sort_order' => $baseSortOrder + $position]);
            $position++;
        }
    }

    public function setThumbnail(Product $product, mixed $imageId): void
    {
        $product->images()->update(['is_thumbnail' => false]);

        if ($imageId === null || $imageId === '') {
            $this->ensureThumbnail($product);

            return;
        }

        $image = $product->images()->whereKey((int) $imageId)->first();

        if ($image !== null && ! $image->isVideo()) {
            $image->update(['is_thumbnail' => true]);

            return;
        }

        $this->ensureThumbnail($product);
    }

    public function ensureThumbnail(Product $product): void
    {
        $product->load('images');

        $marked = $product->images->firstWhere('is_thumbnail', true);
        if ($marked !== null && ! $marked->isVideo()) {
            return;
        }

        $product->images()->update(['is_thumbnail' => false]);

        $firstPhoto = $product->images->first(fn (ProductImage $image): bool => ! $image->isVideo());
        if ($firstPhoto !== null) {
            $firstPhoto->update(['is_thumbnail' => true]);
        }
    }
}
