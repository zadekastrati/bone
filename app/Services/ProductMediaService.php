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
    public function storeUploads(Product $product, array $files, int $startSortOrder = 0): void
    {
        $sortOrder = $startSortOrder;

        foreach ($files as $file) {
            if ($file === null || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('products', 'public');
            $product->images()->create([
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
