<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $fillable = ['name', 'color'];

    public static function colorOptions(): array
    {
        return [
            '#DDF6B7' => 'Зелёный',
            '#C6E3FF' => 'Голубой',
            '#E5D8B5' => 'Песочный',
        ];
    }

    public static function colorOptionsWithSwatches(): array
    {
        return collect(self::colorOptions())
            ->mapWithKeys(fn (string $label, string $color): array => [
                $color => sprintf(
                    '<span class="tag-color-option"><span class="tag-color-option__swatch" style="background-color: %s;"></span><span>%s</span></span>',
                    $color,
                    e($label),
                ),
            ])
            ->all();
    }

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function labelFor(string $locale): string
    {
        return (string) ($this->name[$locale] ?? $this->name['ru'] ?? '');
    }

    public function colorValue(): string
    {
        $color = strtoupper(trim((string) $this->color));

        return array_key_exists($color, self::colorOptions()) ? $color : '#DDF6B7';
    }

    public function colorLabel(): string
    {
        return self::colorOptions()[$this->colorValue()];
    }
}
