<?php

namespace App\Support;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

class VideoThumbnailer
{
    /**
     * Extracts a single frame from a video as a resized JPEG, via ffmpeg.
     * Needs a real file on disk (not just bytes in memory) — ffmpeg has to
     * seek within the container format, which it can't do against a raw
     * byte string. Returns null on any failure (missing ffmpeg binary,
     * corrupt/unsupported video, timeout, ...) so callers can fall back
     * gracefully instead of erroring the whole request.
     */
    public static function extractFrame(string $videoBytes, int $maxDimension, float $seekSeconds = 0.5): ?string
    {
        $dir = sys_get_temp_dir();
        $input = $dir.DIRECTORY_SEPARATOR.'vid-in-'.Str::random(16);
        $output = $dir.DIRECTORY_SEPARATOR.'vid-out-'.Str::random(16).'.jpg';

        file_put_contents($input, $videoBytes);

        try {
            $result = Process::timeout(20)->run([
                'ffmpeg', '-y',
                '-ss', (string) $seekSeconds,
                '-i', $input,
                '-frames:v', '1',
                '-vf', "scale='min({$maxDimension},iw)':-2",
                '-q:v', '4',
                $output,
            ]);

            if (! $result->successful() || ! file_exists($output)) {
                return null;
            }

            return file_get_contents($output) ?: null;
        } catch (\Throwable $e) {
            return null;
        } finally {
            @unlink($input);
            @unlink($output);
        }
    }
}
