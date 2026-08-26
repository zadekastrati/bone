<?php

namespace App\Console\Commands;

use App\Services\ImageVariantCache;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class WarmMediaLibraryCommand extends Command
{
    protected $signature = 'media:warm-library';

    protected $description = 'Pre-generate the admin picker thumbnail for every image and video already on R2, so browsing "Choose from library" never pays the cold R2-fetch-and-resize/ffmpeg cost live';

    /** Mirrors MediaLibraryController::THUMBNAIL_EXTENSIONS / THUMBNAIL_MAX_DIMENSION. */
    private const THUMBNAIL_EXTENSIONS = ['jpeg', 'jpg', 'png', 'webp', 'mp4', 'webm', 'mov', 'ogg', 'm4v'];

    private const THUMBNAIL_MAX_DIMENSION = 400;

    public function handle(ImageVariantCache $variantCache): int
    {
        $disk = Storage::disk('public');

        $paths = collect($disk->allFiles())
            ->reject(fn (string $path): bool => str_starts_with($path, 'thumbnails/'))
            ->filter(function (string $path): bool {
                $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                return in_array($ext, self::THUMBNAIL_EXTENSIONS, true);
            })
            ->values();

        $bar = $this->output->createProgressBar($paths->count());
        $warmed = 0;
        $failed = 0;

        foreach ($paths as $path) {
            $cacheKey = 'thumbnails/'.sha1($path).'.jpg';

            try {
                if ($variantCache->resolve($path, $cacheKey, self::THUMBNAIL_MAX_DIMENSION) !== null) {
                    $warmed++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->warn("Failed to warm {$path}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Warmed {$warmed} image(s), {$failed} failure(s).");

        return self::SUCCESS;
    }
}
