<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class ImageProcessor
{
    public function store(UploadedFile $file, string $directory = 'uploads', ?string $ratio = null): string
    {
        // A gallery can process several large uploads in one Livewire request.
        // Keep the request alive long enough for GD, but do not disable the
        // execution limit globally or leave image processing unbounded.
        if (function_exists('set_time_limit')) {
            @set_time_limit(120);
        }

        $maxDimension = max(320, (int) SiteSetting::getValue('images.max_dimension', 2400));
        $quality = min(100, max(20, (int) SiteSetting::getValue('images.avif_quality', 60)));
        $image = Image::read($file->getRealPath() ?: $file->getPathname());

        // Resize the source before crop. Cropping a 6000px+ source first
        // creates another huge GD bitmap and can exceed shared-host limits
        // when a gallery contains several photos.
        $image->scaleDown(width: $maxDimension, height: $maxDimension);

        if ($ratio !== null) {
            $this->cropToRatio($image, $ratio);
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.avif';
        $this->disk()->put($path, $image->toAvif($quality));

        return $path;
    }

    private function cropToRatio(mixed $image, string $ratio): void
    {
        [$targetWidth, $targetHeight] = array_pad(
            array_map('floatval', preg_split('/\s*[:\/]\s*/', trim($ratio)) ?: []),
            2,
            0,
        );

        if ($targetWidth <= 0 || $targetHeight <= 0) {
            return;
        }

        $sourceWidth = $image->width();
        $sourceHeight = $image->height();

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            return;
        }

        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = $targetWidth / $targetHeight;

        if (abs($sourceRatio - $targetRatio) < 0.0001) {
            return;
        }

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = max(1, (int) round($sourceHeight * $targetRatio));
            $offsetX = (int) floor(($sourceWidth - $cropWidth) / 2);
            $offsetY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = max(1, (int) round($sourceWidth / $targetRatio));
            $offsetX = 0;
            $offsetY = (int) floor(($sourceHeight - $cropHeight) / 2);
        }

        $image->crop($cropWidth, $cropHeight, $offsetX, $offsetY);
    }

    public function disk(): Filesystem
    {
        return Storage::disk('public');
    }
}
