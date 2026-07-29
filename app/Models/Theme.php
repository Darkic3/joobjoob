<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = ['name', 'slug', 'preview_color', 'is_default', 'css_variables'];

    protected $casts = [
        'is_default' => 'boolean',
        'css_variables' => 'array',
    ];

    public static function defaultVariables(): array
    {
        return [
            'bg'              => '#e0e5ec',
            'bg-elevated'     => '#e0e5ec',
            'shadow-light'    => '#ffffff',
            'shadow-dark'     => '#a3b1c6',
            'text-primary'    => '#2d3748',
            'text-secondary'  => '#6b7788',
            'accent'          => '#6c63ff',
            'accent-contrast' => '#ffffff',
            'danger'          => '#e53e5c',
            'success'         => '#38a169',
        ];
    }

    public function mergedVariables(): array
    {
        // ترکیب متغیرهای پیش‌فرض با متغیرهای ذخیره‌شده (اگر وجود داشته باشند)
        return array_merge(self::defaultVariables(), array_filter($this->css_variables ?? []));
    }
}
