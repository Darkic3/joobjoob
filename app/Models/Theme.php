<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * تم‌های از پیش تعریف‌شده که ادمین می‌تواند یکی را به عنوان تم پیش‌فرض سایت انتخاب کند.
 * slug باید با نام کلاس CSS در neumorphism.css مطابقت داشته باشد (مثلاً data-theme="dark-neumorphism")
 */
class Theme extends Model
{
    protected $fillable = ['name', 'slug', 'preview_color', 'is_default'];

    protected $casts = ['is_default' => 'boolean'];
}
