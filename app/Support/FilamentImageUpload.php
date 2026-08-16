<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Throwable;

class FilamentImageUpload
{
    /** Public news and opportunity cards use aspect-ratio: 2.2 / 1. */
    public const CARD_RATIO = '11:5';

    /** Public standalone article images use a wide 16:9 frame. */
    public const ARTICLE_RATIO = '16:9';

    public const LANDSCAPE_RATIO = '4:3';

    public const PORTRAIT_RATIO = '3:4';

    /**
     * FilePond runs in the visitor's browser, so existing uploads must use a
     * relative public URL. Storage::url() would otherwise use APP_URL, which
     * may differ from the active local domain or a deployed domain.
     */
    public static function configure(FileUpload $upload): FileUpload
    {
        return $upload
            // Nested Builder/Repeater uploads must survive the next Livewire
            // hydration until the form is submitted. The callback below still
            // verifies stored files when it builds the preview URL.
            ->fetchFileInformation(false)
            ->getUploadedFileUsing(static function (FileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                try {
                    $disk = $component->getDisk();

                    if (! $disk->exists($file)) {
                        return null;
                    }

                    return [
                        'name' => ($component->isMultiple() ? ($storedFileNames[$file] ?? null) : $storedFileNames) ?? basename($file),
                        'size' => $disk->size($file),
                        'type' => $disk->mimeType($file),
                        'url' => self::relativePublicUrl($file),
                    ];
                } catch (Throwable) {
                    return null;
                }
            });
    }

    /**
     * Every upload variant uses FilePond's native fixed frame. The same ratio
     * is applied again by ImageProcessor before the AVIF is stored, so a
     * reopened form and the public page cannot disagree about the crop.
     */
    public static function fixedGrid(FileUpload $upload, string $ratio): FileUpload
    {
        return static::configure($upload)
            ->panelLayout('integrated')
            ->panelAspectRatio($ratio)
            ->itemPanelAspectRatio($ratio)
            ->imageCropAspectRatio($ratio);
    }

    public static function relativePublicUrl(string $path): string
    {
        $segments = array_map('rawurlencode', explode('/', ltrim($path, '/')));

        return '/storage/'.implode('/', $segments);
    }
}
