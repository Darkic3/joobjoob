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
        $arabic  = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $latin   = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $input = str_replace($persian, $latin, $input);
        $input = str_replace($arabic, $latin, $input);

        return $input;
    }

    /**
     * فرمت عددی با جداکننده هزار (تومان)
     */
    public static function formatToman($amount): string
    {
        return number_format(intval($amount));
    }

    /**
     * حذف جداکننده و تبدیل به عدد
     */
    public static function cleanNumber($input): int
    {
        $cleaned = self::persian2LatinDigit($input);
        $cleaned = str_replace(',', '', $cleaned);
        $cleaned = str_replace(' ', '', $cleaned);

        return intval($cleaned);
    }
}
