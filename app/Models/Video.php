<?php

namespace App\Models;

use App\Support\FilamentImageUpload;
use App\Support\YouTube;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title', 'description', 'youtube_url', 'youtube_id', 'event_date', 'cover_image', 'position',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'event_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $video): void {
            if (! $video->position) {
                $video->position = ((int) static::max('position')) + 1;
            }
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    public function titleFor(string $locale): string
    {
        return (string) ($this->title[$locale] ?? $this->title['ru'] ?? '');
    }

    public function descriptionFor(string $locale): string
    {
        return (string) ($this->description[$locale] ?? $this->description['ru'] ?? '');
    }

    public function thumbnailUrl(): string
    {
        return YouTube::thumbnailUrl((string) $this->youtube_id);
    }

    public function coverUrl(): string
    {
        return filled($this->cover_image)
            ? FilamentImageUpload::relativePublicUrl((string) $this->cover_image)
            : $this->thumbnailUrl();
    }
}
