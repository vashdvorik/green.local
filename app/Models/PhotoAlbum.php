<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoAlbum extends Model
{
    protected $fillable = [
        'slug', 'status', 'published_at', 'cover_image', 'title', 'excerpt',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'title' => 'array',
            'excerpt' => 'array',
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
}
