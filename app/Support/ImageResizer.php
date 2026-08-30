<?php

namespace App\Support;

class ImageResizer
{
    /**
     * Downscales an image to fit within $maxDimension (longest edge) and
     * re-encodes it as JPEG. Returns the original bytes unchanged if GD
     * can't decode them (falls back gracefully instead of erroring).
     */
    public static function resize(string $original, int $maxDimension, int $quality = 88): string
    {
        self::ensureEnoughMemoryFor($original);

        $source = @imagecreatefromstring($original);
        if ($source === false) {
            return $original;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        // imagecopyresampled (bicubic-like) instead of imagescale()'s default
        // IMG_BILINEAR_FIXED mode, which is visibly softer/blurrier — the
        // cause of thumbnails looking noticeably lower quality than their
        // full-resolution R2 originals despite no change to the source file.
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
        imagedestroy($source);

        ob_start();
        imagejpeg($resized, null, $quality);
        $bytes = (string) ob_get_clean();
        imagedestroy($resized);

        return $bytes;
    }

    /**
     * GD decodes an image fully into memory before this class ever gets a
     * chance to shrink it — a 24-30MP camera JPEG (common for real product
     * photos, not the small test fixtures this was originally verified
     * against) needs width x height x ~4 bytes just for the decoded bitmap,
     * easily 100MB+, which silently exceeds a container's default PHP
     * memory_limit (128M is a common stock default). That failure happens
     * inside GD's C code as a hard fatal, not a catchable PHP exception, so
     * there's no way to recover from it after the fact — it has to be
     * prevented by ensuring enough headroom before imagecreatefromstring()
     * ever runs. Formula follows WordPress's wp_raise_memory_limit(), the
     * standard approach for this exact class of problem.
     */
    private static function ensureEnoughMemoryFor(string $original): void
    {
        $currentLimit = self::iniBytes(ini_get('memory_limit'));
        if ($currentLimit === -1) {
            return;
        }

        $info = @getimagesizefromstring($original);
        if ($info === false) {
            return;
        }

        [$width, $height] = $info;
        $channels = $info['channels'] ?? 4;
        $needed = (int) round($width * $height * ($channels + 1) * 1.65) + (16 * 1024 * 1024);

        if ($needed > $currentLimit) {
            @ini_set('memory_limit', (string) min($needed, 1024 * 1024 * 1024));
        }
    }

    private static function iniBytes(string|false $value): int
    {
        if ($value === false || $value === '' || $value === '-1') {
            return -1;
        }

        $unit = strtolower(substr($value, -1));
        $number = (int) $value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    }
}
