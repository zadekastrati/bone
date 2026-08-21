<?php

namespace App\Support;

class ImageResizer
{
    /**
     * Downscales an image to fit within $maxDimension (longest edge) and
     * re-encodes it as JPEG. Returns the original bytes unchanged if GD
     * can't decode them (falls back gracefully instead of erroring).
     */
    public static function resize(string $original, int $maxDimension, int $quality = 78): string
    {
        $source = @imagecreatefromstring($original);
        if ($source === false) {
            return $original;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maxDimension / max($width, $height));
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $resized = imagescale($source, $targetWidth, $targetHeight);
        imagedestroy($source);

        if ($resized === false) {
            return $original;
        }

        ob_start();
        imagejpeg($resized, null, $quality);
        $bytes = (string) ob_get_clean();
        imagedestroy($resized);

        return $bytes;
    }
}
