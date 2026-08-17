<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoAlbum extends Model
{
    protected $fillable = [
        'slug', 'status', 'published_at', 'cover_image', 'title', 'excerpt', 'content', 'photo_content',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'title' => 'array',
            'excerpt' => 'array',
            'content' => 'array',
            'photo_content' => 'array',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('position')->orderBy('id');
    }

    public function titleFor(string $locale): string
    {
        return (string) ($this->title[$locale] ?? $this->title['ru'] ?? '');
    }

    public function excerptFor(string $locale): string
    {
        return (string) ($this->excerpt[$locale] ?? $this->excerpt['ru'] ?? '');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at?->lte(now());
    }

    /**
     * Return the new photo builder content, or adapt legacy relation rows into
     * the same gallery blocks so existing albums remain visible after the
     * editor is migrated.
     *
     * @return array<int, array<string, mixed>>
     */
    public function contentFor(string $locale): array
    {
        $sharedContent = $this->photo_content;

        if (is_array($sharedContent) && count($sharedContent)) {
            return array_values($sharedContent);
        }

        $content = data_get($this->content, $locale);

        if (is_array($content) && count($content)) {
            return array_values($content);
        }

        $photos = $this->relationLoaded('photos') ? $this->photos : $this->photos()->get();
        $paths = $photos->pluck('path')->filter()->values();

        return $paths->chunk(4)->map(function ($group): array {
            $images = $group->map(fn (string $path): array => ['path' => $path])->values()->all();
            $count = count($images);

            return $count === 1
                ? ['type' => 'image', 'data' => $images[0]]
                : ['type' => "gallery_{$count}", 'data' => ['images' => $images]];
        })->values()->all();
    }

    public function photoCount(): int
    {
        return collect($this->contentFor('ru'))->sum(function (array $block): int {
            return match ($block['type'] ?? null) {
                'image' => filled(data_get($block, 'data.path')) ? 1 : 0,
                'gallery_2', 'gallery_3', 'gallery_4' => collect(data_get($block, 'data.images', []))
                    ->filter(fn (mixed $image): bool => filled(data_get($image, 'path')))
                    ->count(),
                default => 0,
            };
        });
    }
}
