<?php

if (! function_exists('format_card_number')) {
    /**
     * فرمت‌دهی شماره کارت برای نمایش: 6037123456789012 → 6037-1234-5678-9012
     */
    function format_card_number(?string $card): string
    {
        $clean = preg_replace('/[^0-9]/', '', $card ?? '');

        if (strlen($clean) !== 16) {
            return $card ?? '';
        }

        return implode('-', str_split($clean, 4));
    }
}

if (! function_exists('clean_card_number')) {
    /**
     * حذف تمام کاراکترهای غیرعددی از شماره کارت برای ذخیره در دیتابیس
     */
    function clean_card_number(?string $card): ?string
    {
        $clean = preg_replace('/[^0-9]/', '', $card ?? '');

        return $clean === '' ? null : $clean;
    }
}
