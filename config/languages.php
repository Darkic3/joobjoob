<?php

/*
|--------------------------------------------------------------------------
| کاتالوگ زبان‌های شناخته‌شده
|--------------------------------------------------------------------------
| این فایل «متادیتای پایه» هر زبان را می‌داند: جهت، خانواده‌ی تقویم و
| خانواده‌ی ارقام. حقیقتِ «کدام زبان فعال/پیش‌فرض است» در جدول
| languages است (مدیریت‌پذیر از پنل ادمین).
|
| `_default` برای زبان‌های ناشناخته (مثلاً یک زبان جدید که هنوز به
| کاتالوگ اضافه نشده) استفاده می‌شود — منطقی و امن.
|
| کلیدها:
|   dir      → rtl | ltr
|   calendar → jalali | gregorian
|   digits   → persian | arabic | latin
*/

return [
    '_default' => [
        'dir' => 'ltr',
        'calendar' => 'gregorian',
        'digits' => 'latin',
    ],

    'fa' => ['native' => 'فارسی',   'dir' => 'rtl',  'calendar' => 'jalali',    'digits' => 'persian'],
    'ar' => ['native' => 'العربية',  'dir' => 'rtl',  'calendar' => 'jalali',    'digits' => 'arabic'],
    'en' => ['native' => 'English',  'dir' => 'ltr',  'calendar' => 'gregorian', 'digits' => 'latin'],
    'tr' => ['native' => 'Türkçe',   'dir' => 'ltr',  'calendar' => 'gregorian', 'digits' => 'latin'],
];
