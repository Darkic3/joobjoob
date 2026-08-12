<?php

use Carbon\Carbon;

if (! function_exists('show_date')) {
    /**
     * نمایش تاریخ بر اساس زبان فعال سایت.
     *
     * تصمیم‌گیری داده‌محور است (LocaleService::calendar از کاتالوگ/جدول زبان‌ها):
     * - تقویم jalali (فارسی و هر زبان شمسیِ ثبت‌شده در کاتالوگ) → شمسی با ارقام فارسی
     * - تقویم hijri (عربی و هر زبان قمریِ ثبت‌شده) → قمری با نام ماه عربی و ارقام عربی
     * - تقویم gregorian (انگلیسی، ترکی و هر زبان جدیدِ ثبت‌نشده) → میلادی با نام ماه محلی
     *
     * هر سه مسیر در منطقه زمانی تهران تفسیر/نمایش می‌شوند تا با رفتار
     * jdate_format هماهنگ بمانند. دیتابیس همیشه میلادی (معمولاً UTC) است.
     */
    function show_date(string|int|DateTimeInterface|null $timestamp = null, string $format = 'Y/m/d H:i'): string
    {
        $locale = app()->getLocale();

        if ($timestamp === null) {
            $ts = time();
        } elseif (is_numeric($timestamp)) {
            $ts = (int) $timestamp;
        } elseif ($timestamp instanceof DateTimeInterface) {
            $ts = $timestamp->getTimestamp();
        } else {
            try {
                $ts = Carbon::parse($timestamp, 'Asia/Tehran')->getTimestamp();
            } catch (Throwable $e) {
                $ts = time();
            }
        }

        $calendar = locale()->calendar($locale);

        // شمسی
        if ($calendar === 'jalali' && function_exists('jdate_format')) {
            return jdate_format($format, $ts);
        }

        // قمری
        if ($calendar === 'hijri' && function_exists('hijri_date')) {
            return hijri_date($format, $ts);
        }

        // سایر زبان‌ها: میلادی با نام ماه/روز به زبان محلی (en → English, tr → Türkçe)
        return Carbon::createFromTimestamp($ts, 'Asia/Tehran')
            ->locale($locale ?: 'en')
            ->translatedFormat($format);
    }
}
