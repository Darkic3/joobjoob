<?php

use Carbon\Carbon;

if (! function_exists('show_date')) {
    /**
     * نمایش تاریخ بر اساس زبان فعال سایت.
     *
     * تصمیم‌گیری داده‌محور است (LocaleService::isJalali از کاتالوگ/جدول زبان‌ها):
     * - تقویم jalali (فارسی/عربی و هر زبان شمسیِ ثبت‌شده در کاتالوگ) → شمسی با ارقام فارسی
     * - تقویم gregorian (انگلیسی، ترکی و هر زبان جدیدِ ثبت‌نشده) → میلادی با نام ماه محلی
     *
     * هر دو مسیر در منطقه زمانی تهران تفسیر/نمایش می‌شوند تا با رفتار
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

        // تقویم از کاتالوگ/جدول زبان‌ها (فارسی و عربی → شمسی؛ سایر → میلادی)
        // تا زبان‌های جدیدِ شمسی بدون تغییر کد اضافه شوند
        if (locale()->isJalali($locale) && function_exists('jdate_format')) {
            return jdate_format($format, $ts);
        }

        // سایر زبان‌ها: میلادی با نام ماه/روز به زبان محلی (en → English, tr → Türkçe)
        return Carbon::createFromTimestamp($ts, 'Asia/Tehran')
            ->locale($locale ?: 'en')
            ->translatedFormat($format);
    }
}
