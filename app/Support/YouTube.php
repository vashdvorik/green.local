<?php

namespace App\Support;

use Illuminate\Support\Str;

class YouTube
{
    public static function extractVideoId(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));
        $host = Str::startsWith($host, 'www.') ? Str::after($host, 'www.') : $host;

        if (! in_array($host, ['youtube.com', 'youtu.be'], true)) {
            return null;
        }

        $path = trim((string) ($parts['path'] ?? ''), '/');
        $id = null;

        if ($host === 'youtu.be') {
            $id = Str::before($path, '/');
        } elseif ($path === 'watch') {
            parse_str((string) ($parts['query'] ?? ''), $query);
            $id = $query['v'] ?? null;
        } elseif (Str::startsWith($path, ['shorts/', 'embed/', 'live/'])) {
            $id = Str::after($path, '/');
        }

        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) === 1
            ? $id
            : null;
    }

    public static function thumbnailUrl(string $videoId): string
    {
        return "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg";
    }
}
