<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * تبدیل اعداد فارسی و عربی به لاتین
     */
    public static function persian2LatinDigit($input): string
    {
        if (empty($input)) {
            return $input;
        }

        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $input = str_replace($persian, $latin, $input);
        $input = str_replace($arabic, $latin, $input);

        return $input;
    }

    /**
     * تبدیل ارقام لاتین به فارسی/عربی بر اساس خانوادهٔ ارقام زبان جاری.
     * برای رندر سمت سرور بج‌ها و شمارنده‌ها تا قبل از اجرای JS چشمک
     * انگلیسی→فارسی دیده نشود (هماهنگ با localize-digits.js).
     */
    public static function localizeDigits($input, ?string $family = null): string
    {
        $family ??= app()->getLocale() === 'ar' ? 'arabic' : (app()->getLocale() === 'fa' ? 'persian' : 'latin');

        $s = (string) $input;

        if ($family === 'persian') {
            return str_replace(
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
                $s
            );
        }

        if ($family === 'arabic') {
            return str_replace(
                ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
                ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
                $s
            );
        }

        return $s;
    }

    /**
     * فرمت عددی با جداکننده هزار (تومان)
     */
    public static function formatToman($amount): string
    {
        return number_format(intval($amount));
    }

    /**
     * فرمت قیمت با جداکننده هزار متناسب با زبان جاری
     *
     * fa/ar -> ۵٬۰۰۰٬۰۰۰
     * en/tr -> 5,000,000
     */
    public static function formatPrice($amount, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        $separator = match ($locale) {
            'fa', 'ar' => self::ARABIC_THOUSANDS_SEP,
            default => ',',
        };

        return number_format(intval($amount), 0, '.', $separator);
    }

    /**
     * جداکننده هزار عربی/فارسی (٬)
     */
    private const ARABIC_THOUSANDS_SEP = "\u{066C}";
}
