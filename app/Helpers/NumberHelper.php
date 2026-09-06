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

    // ─────────────────────────────────────────────────────────────────────────────
    // بومی‌سازی ارقام کلِ خروجی HTML (سمت سرور — قبل از اولین paint)
    //
    // «قانون طلایی» بومی‌سازی ارقام در این پروژه:
    //   رقم‌ها باید از همان لحظهٔ ساختِ متن در خانوادهٔ درست باشند؛ نه اینکه بعداً
    //   توسط JS اصلاح شوند. این متد روی متنِ بینِ تگ‌ها اعمال می‌شود (وسطِ پاسخ HTML)
    //   و دقیقاً همان قواعد localize-digits.js را دنبال می‌کند تا نتیجه با حالت
    //   نهاییِ سمت کلاینت یکسان باشد — فقط زودتر (بدون چشمک انگلیسی→فارسی):
    //
    //   - فقط متنِ قابل مشاهده تبدیل می‌شود؛ صفت‌ها (attributes)، URLها و مقادیر
    //     ورودی هرگز دست نمی‌خورند.
    //   - محتوای script / style / textarea / pre / code / noscript (داده/کد)
    //     و متنِ select / option (داده‌ی فرم) تبدیل نمی‌شود.
    //   - زیردرختِ عناصر [data-digit-raw]، .hero-num یا [contenteditable="true"]
    //     همان‌طور که JS رد می‌کند، اینجا هم رد می‌شود.
    //   - موجودیت‌های HTML (مثل &amp; و &#1234;) دست‌نخورده می‌مانند.
    //   - تبدیل روی پاسخ‌های فارسی/عربی (لاتین → خانواده) و لاتین
    //     (فارسی/عربی → لاتین) هر دو اعمال می‌شود — هماهنگ با convertText توی JS.
    // ─────────────────────────────────────────────────────────────────────────────

    /** محتوای «خام»: می‌تواند < داشته باشد و باید تا تگِ بسته یکجا رد شود */
    private const RAW_TAGS = ['script', 'style', 'textarea', 'pre', 'code', 'noscript'];

    /** متن این‌ها «داده‌ی فرم» تلقی می‌شود (JS هم گزینه‌های select را رد می‌کند) */
    private const SKIP_FORM_TAGS = ['select', 'option'];

    /** تگ‌های بی‌تگ-بسته (void) — هرگز روی پشتهٔ رد قرار نمی‌گیرند */
    private const VOID_TAGS = ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'];

    /**
     * بومی‌سازی ارقامِ متنِ یک رشتهٔ HTML (بدون دست‌زدن به تگ‌ها/صفت‌ها).
     */
    public static function localizeHtmlText(string $html, ?string $family = null): string
    {
        $family ??= self::currentFamily();

        $len = strlen($html);
        $out = '';
        $skipStack = [];   // نامِ تگ‌هایی که متنِ زیردرختشان نباید تبدیل شود
        $i = 0;

        while ($i < $len) {
            $lt = strpos($html, '<', $i);

            if ($lt === false) {
                // متن باقی‌مانده تا پایان
                $out .= empty($skipStack)
                    ? self::localizeText(substr($html, $i), $family)
                    : substr($html, $i);
                break;
            }

            // متن قبل از تگ
            if ($lt > $i) {
                $out .= empty($skipStack)
                    ? self::localizeText(substr($html, $i, $lt - $i), $family)
                    : substr($html, $i, $lt - $i);
            }

            // تگ کامل (مقاوم به '>' داخل مقدار صفت‌های کوتیشن‌دار)
            $tag = self::readTag($html, $lt, $end);
            if ($tag === '') {
                // '>' پیدا نشد — بقیه را متنِ عادی بگیر
                $out .= empty($skipStack)
                    ? self::localizeText(substr($html, $lt), $family)
                    : substr($html, $lt);
                break;
            }

            $out .= $tag;

            if (! empty($skipStack)) {
                // داخل ناحیهٔ رد — فقط بستنِ تگِ رویِ پشته مهم است
                if (self::isClosingTag($tag) && end($skipStack) === self::tagName($tag)) {
                    array_pop($skipStack);
                }
                $i = $end + 1;
                continue;
            }

            $name = self::tagName($tag);
            $isOpen = $name !== ''
                && ! self::isClosingTag($tag)
                && ! self::isSelfClosing($tag)
                && ! in_array($name, self::VOID_TAGS, true);

            if ($isOpen) {
                $isRaw = in_array($name, self::RAW_TAGS, true);
                $isFormData = in_array($name, self::SKIP_FORM_TAGS, true);
                $byAttr = (bool) preg_match('/\bdata-digit-raw\b/', $tag)
                    || (bool) preg_match('/\bhero-num\b/', $tag)
                    || (bool) preg_match('/contenteditable\s*=\s*"true"/i', $tag);

                if ($isRaw) {
                    // محتوای خام ممکن است '<' داشته باشد (JS/CSS/نمونه‌کد) —
                    // تا تگِ بستهٔ همنام با یک جستجو رد می‌شود و دست نمی‌خورد.
                    $close = self::findRawClose($html, $end + 1, $name);
                    if ($close === null) {
                        $out .= substr($html, $end + 1);
                        break;
                    }
                    $out .= substr($html, $end + 1, $close - ($end + 1));
                    $out .= self::readTag($html, $close, $end);  // تگ بسته را هم کپی کن
                } elseif ($isFormData || $byAttr) {
                    $skipStack[] = $name;
                }
            }

            $i = $end + 1;
        }

        return $out;
    }

    /**
     * تگِ کامل از '<' تا '>' (با احترام به کوتیشن‌های داخل صفت‌ها) را برمی‌گرداند.
     * اگر '>' پیدا نشود رشتهٔ خالی و $end نامعتبر برمی‌گردد.
     */
    private static function readTag(string $html, int $lt, ?int &$end): string
    {
        $len = strlen($html);
        $quote = null;
        $j = $lt + 1;

        for (; $j < $len; $j++) {
            $c = $html[$j];
            if ($quote !== null) {
                if ($c === $quote) {
                    $quote = null;
                }
                continue;
            }
            if ($c === '"' || $c === "'") {
                $quote = $c;
                continue;
            }
            if ($c === '>') {
                break;
            }
        }

        if ($j >= $len) {
            $end = null;

            return '';
        }

        $end = $j;

        return substr($html, $lt, $j - $lt + 1);
    }

    /**
     * اندیس '<' تگ بستهٔ بعدیِ همنام (مثل </script>) را از $from به بعد پیدا می‌کند.
     */
    private static function findRawClose(string $html, int $from, string $name): ?int
    {
        $len = strlen($html);
        while ($from < $len) {
            $pos = stripos($html, '</'.$name, $from);
            if ($pos === false) {
                return null;
            }
            $after = $pos + strlen($name) + 2;
            $next = $html[$after] ?? '';
            if ($next === '>' || $next === ' ' || $next === "\t" || $next === "\r" || $next === "\n") {
                return $pos;
            }
            $from = $pos + 2; // احتمالش هست «</textarea» وسطِ متن آمده باشد
        }

        return null;
    }

    private static function isClosingTag(string $tag): bool
    {
        return str_starts_with(ltrim($tag), '</');
    }

    private static function isSelfClosing(string $tag): bool
    {
        return str_ends_with(rtrim($tag), '/>');
    }

    private static function tagName(string $tag): string
    {
        if (preg_match('#^<\s*/?\s*([a-zA-Z][a-zA-Z0-9:-]*)#', $tag, $m)) {
            return strtolower($m[1]);
        }

        return '';
    }

    /**
     * تبدیل ارقام و جداکنندهٔ متن بر اساس خانواده — فقط خودِ متن، بدون تگ.
     * (منطق مشابه convertText در localize-digits.js؛ برای تست‌ها و مواردی که
     * فقط یک رشتهٔ متنی باید بومی شود هم در دسترس است)
     */
    public static function localizeText(string $text, ?string $family = null): string
    {
        $family ??= self::currentFamily();

        if ($text === '' || ! preg_match('/[0-9۰-۹٠-٩٪٬,]/', $text)) {
            return $text;
        }

        // موجودیت‌های HTML (مثل &#48;) را موقتاً محافظت کن تا ارقام داخلشان تبدیل نشود
        $entities = [];
        $protected = preg_replace_callback(
            '/&(?:#[0-9]{1,7}|#x[0-9a-fA-F]{1,6}|[a-zA-Z][a-zA-Z0-9]{1,31});/',
            function (array $m) use (&$entities) {
                // توکنِ نگهدارنده باید فقط حرف داشته باشد — اگر رقم/جداکننده داشته
                // باشد خودِ توکن تبدیل می‌شود و بازیابی خراب می‌شود.
                $k = "\x1A".str_repeat('e', count($entities) + 1)."\x1A";
                $entities[$k] = $m[0];

                return $k;
            },
            $text
        );

        $converted = strtr((string) $protected, self::digitMaps($family));

        return $entities === [] ? $converted : strtr($converted, $entities);
    }

    /**
     * نگاشت کاراکترهای عددی/جداکننده برای هر خانوادهٔ ارقام
     */
    private static function digitMaps(string $family): array
    {
        return match ($family) {
            'persian' => [
                '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
                '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
                ',' => '٬',
            ],
            'arabic' => [
                '0' => '٠', '1' => '١', '2' => '٢', '3' => '٣', '4' => '٤',
                '5' => '٥', '6' => '٦', '7' => '٧', '8' => '٨', '9' => '٩',
                ',' => '٬',
            ],
            default => [
                '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
                '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
                '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
                '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
                '٪' => '%', '٬' => ',',
            ],
        };
    }

    /**
     * خانوادهٔ ارقام زبان جاری (persian|arabic|latin)
     */
    private static function currentFamily(): string
    {
        return match (app()->getLocale()) {
            'ar' => 'arabic',
            'fa' => 'persian',
            default => 'latin',
        };
    }
}
