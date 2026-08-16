<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $value = json_decode((string) $setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $value : $setting->value;
    }

    public static function putValue(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)],
        );
    }

    public static function getEncrypted(string $key, ?string $default = null): ?string
    {
        $value = static::getValue($key);

        if (! is_string($value) || $value === '') {
            return $default;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $default;
        }
    }

    public static function putEncrypted(string $key, ?string $value): void
    {
        static::putValue($key, filled($value) ? Crypt::encryptString($value) : null);
    }
}
