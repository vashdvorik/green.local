<?php

namespace App\Support;

use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Illuminate\Support\HtmlString;
use Throwable;

class RichText
{
    public static function toHtml(mixed $content): string
    {
        if ($content instanceof HtmlString) {
            return $content->toHtml();
        }

        if (blank($content)) {
            return '';
        }

        if (is_string($content)) {
            return $content;
        }

        if (is_array($content)) {
            try {
                return RichContentRenderer::make($content)->toHtml();
            } catch (Throwable) {
                return e(static::toText($content));
            }
        }

        return e((string) $content);
    }

    public static function toText(mixed $content): string
    {
        if (blank($content)) {
            return '';
        }

        if (is_string($content)) {
            if (str_contains($content, '<')) {
                try {
                    return trim(RichContentRenderer::make($content)->toText());
                } catch (Throwable) {
                    return trim(strip_tags($content));
                }
            }

            return trim($content);
        }

        if (! is_array($content)) {
            return trim((string) $content);
        }

        try {
            return RichContentRenderer::make($content)->toText();
        } catch (Throwable) {
            return trim(json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
        }
    }

    /**
     * Convert a plain-text fallback from a translation provider to the HTML
     * format expected by Filament's RichEditor without losing line breaks.
     */
    public static function plainTextToHtml(string $content): string
    {
        $content = trim($content);

        if ($content === '') {
            return '';
        }

        $paragraphs = preg_split('/(?:\r\n|\r|\n){2,}/', $content) ?: [$content];

        return implode('', array_map(static function (string $paragraph): string {
            $paragraph = e(trim($paragraph));
            $paragraph = str_replace(["\r\n", "\r", "\n"], '<br>', $paragraph);

            return "<p>{$paragraph}</p>";
        }, $paragraphs));
    }
}
