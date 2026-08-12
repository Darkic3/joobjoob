<?php

if (! function_exists('gregorian_to_jd')) {
    /**
     * تبدیل تاریخ میلادی به عدد ژولین (JDN).
     *
     * الگوریتم استاندارد (Calendrical Calculations / Gregorian→JD).
     * برای استفادهٔ داخلی hijri_date — بدون وابستگی به پسوند calendar.
     */
    function gregorian_to_jd(int $g_y, int $g_m, int $g_d): int
    {
        $a = intdiv(14 - $g_m, 12);
        $y = $g_y + 4800 - $a;
        $m = $g_m + 12 * $a - 3;

        return $g_d + intdiv(153 * $m + 2, 5) + 365 * $y
            + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;
    }
}

if (! function_exists('jd_to_hijri')) {
    /**
     * تبدیل عدد ژولین به تاریخ قمری (تقویم اسلامیِ مدنی/جدولی).
     *
     * الگوریتم شناخته‌شدهٔ «Islamic civil» — قطعی و بدون نیاز به ICU:
     * همین فرمول در بسیاری از کتابخانه‌ها استفاده می‌شود
     * (۱ محرم ۱۴۴۵ = ۱۹ ژوئیهٔ ۲۰۲۳ در این تقویم).
     *
     * @return array{0:int, 1:int, 2:int} [سال، ماه، روز]
     */
    function jd_to_hijri(int $jd): array
    {
        $l = $jd - 1948440 + 10632;
        $n = intdiv($l - 1, 10631);
        $l = $l - 10631 * $n + 354;
        $j = intdiv(10985 - $l, 5316) * intdiv(50 * $l, 17719)
            + intdiv($l, 5670) * intdiv(43 * $l, 15238);
        $l = $l - intdiv(30 - $j, 15) * intdiv(17719 * $j, 50)
            - intdiv($j, 16) * intdiv(15238 * $j, 43) + 29;
        $m = intdiv(24 * $l, 709);
        $d = $l - intdiv(709 * $m, 24);
        $y = 30 * $n + $j - 30;

        return [$y, $m, $d];
    }
}

if (! function_exists('hijri_date')) {
    /**
     * نمایش تاریخ قمری (هجری شمسی‌نما/اسلامی) با نام ماه عربی و ارقام عربی.
     *
     * فقط برای زبان‌های با تقویم hijri (مثل ar) — از show_date() صدا زده می‌شود.
     * دیتابیس همیشه میلادی/UTC است؛ نمایش در منطقهٔ زمانیِ داده‌شده (پیش‌فرض تهران).
     *
     * حروف پشتیبانی‌شده (همان حروف پرکاربرد show_date):
     *   d,j  روز | m,n ماه عددی | F نام کامل ماه | M نام ماه | Y,y سال | H,G,i,s ساعت/دقیقه/ثانیه
     * بقیهٔ کاراکترها (جداکننده‌ها مثل / - : و فاصله) همان‌طور منتقل می‌شوند.
     */
    function hijri_date(string $format, int|string $timestamp = ''): string
    {
        $ts = ($timestamp === '' || $timestamp === 'now') ? time() : (int) $timestamp;

        $dt = (new DateTimeImmutable('@'.$ts))->setTimezone(new DateTimeZone('Asia/Tehran'));
        [$gy, $gm, $gd] = [
            (int) $dt->format('Y'),
            (int) $dt->format('n'),
            (int) $dt->format('j'),
        ];

        [$hy, $hm, $hd] = jd_to_hijri(gregorian_to_jd($gy, $gm, $gd));

        $months = [
            'محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر',
            'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان',
            'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة',
        ];

        $out = '';
        $len = strlen($format);
        for ($i = 0; $i < $len; $i++) {
            $sub = $format[$i];
            if ($sub === '\\' && $i + 1 < $len) {
                $out .= $format[++$i];

                continue;
            }
            switch ($sub) {
                case 'd':
                    $out .= ($hd < 10) ? '0'.$hd : (string) $hd;
                    break;
                case 'j':
                    $out .= (string) $hd;
                    break;
                case 'F':
                case 'M':
                    $out .= $months[$hm - 1];
                    break;
                case 'm':
                    $out .= ($hm > 9) ? (string) $hm : '0'.$hm;
                    break;
                case 'n':
                    $out .= (string) $hm;
                    break;
                case 'Y':
                    $out .= (string) $hy;
                    break;
                case 'y':
                    $out .= substr((string) $hy, -2);
                    break;
                case 'H':
                    $out .= $dt->format('H');
                    break;
                case 'G':
                    $out .= (string) (int) $dt->format('G');
                    break;
                case 'i':
                    $out .= $dt->format('i');
                    break;
                case 's':
                    $out .= $dt->format('s');
                    break;
                default:
                    $out .= $sub;
            }
        }

        // ارقام عربی‌-هندی
        return strtr($out, [
            '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
            '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
        ]);
    }
}
