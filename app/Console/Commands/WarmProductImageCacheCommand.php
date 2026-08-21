<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use App\Services\ImageVariantCache;
use Illuminate\Console\Command;

class WarmProductImageCacheCommand extends Command
{
    protected $signature = 'media:warm-product-images';

    protected $description = 'Pre-generate and cache the thumb/display variants for every already-attached product photo, so customers never pay the R2 fetch + resize cost on first view';

    public function handle(ImageVariantCache $variantCache): int
    {
        $images = ProductImage::query()->get();
        $bar = $this->output->createProgressBar($images->count());
        $warmed = 0;
        $skipped = 0;

        foreach ($images as $image) {
            if ($image->isVideo()) {
                $skipped++;
                $bar->advance();

                continue;
            }

            $variants = [];
            foreach (ImageVariantCache::VARIANTS as $variant => $maxDimension) {
                $variants["thumbnails/product-image-{$image->id}-{$variant}.jpg"] = $maxDimension;
            }

            try {
                $variantCache->warm($image->path, $variants);
                $warmed++;
            } catch (\Throwable $e) {
                $this->newLine();
                $this->warn("Failed to warm image #{$image->id} ({$image->path}): {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Warmed {$warmed} image(s), skipped {$skipped} video(s).");

        return self::SUCCESS;
    }
}
