<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key', 'value', 'type', 'group', 'label', 'options', 'sort',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'sort' => 'integer',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'json' => json_decode($setting->value, true),
            'boolean' => (bool) $setting->value,
            default => $setting->value,
        };
    }

    /**
     * آدرس عمومی تصویر آپلودشده برای یک کلید تنظیمات
     */
    public static function imageFile(string $key): ?string
    {
        $value = static::getValue($key);
        if (empty($value)) {
            return null;
        }

        $path = 'uploads/settings/'.$value;

        return file_exists(public_path($path)) ? asset($path) : null;
    }

    /**
     * آدرس عمومی ویدیوی آپلودشده برای یک کلید تنظیمات
     */
    public static function videoFile(string $key): ?string
    {
        $value = static::getValue($key);
        if (empty($value)) {
            return null;
        }

        $path = 'uploads/settings/'.$value;

        return file_exists(public_path($path)) ? asset($path) : null;
    }
}
