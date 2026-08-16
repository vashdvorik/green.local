<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'slug', 'status', 'published_at', 'cover_image', 'title', 'excerpt', 'content',
        'translation_meta', 'author', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'title' => 'array',
            'excerpt' => 'array',
            'content' => 'array',
            'translation_meta' => 'array',
        ];
    }

    public function titleFor(string $locale): string
    {
        return (string) ($this->title[$locale] ?? $this->title['ru'] ?? '');
    }

    public function excerptFor(string $locale): string
    {
        return (string) ($this->excerpt[$locale] ?? $this->excerpt['ru'] ?? '');
    }

    public function contentFor(string $locale): array
    {
        return $this->content[$locale] ?? $this->content['ru'] ?? [];
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
