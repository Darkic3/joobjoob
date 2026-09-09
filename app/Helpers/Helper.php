<?php

namespace App\Helpers;

class Helper
{
    public static function isMobile(): bool
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $mobileKeywords = [
            'android', 'iphone', 'ipad', 'blackberry', 'windows phone', 'kindle', 'symbian', 'webos',
            'opera mini', 'opera mobi', 'ucbrowser', 'iemobile', 'palm', 'maemo', 'meego', 'bada',
            'fennec', 'nokia', 'motorola', 'sonyericsson', 'lg', 'samsung', 'htc', 'zte', 'huawei',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    public static function persian2LatinDigit($string): array|string
    {
        $persian_digits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic_digits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english_digits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $string = str_replace($persian_digits, $english_digits, $string);

        return str_replace($arabic_digits, $english_digits, $string);
    }


}
