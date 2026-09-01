<?php

use App\Helpers\NumberHelper;

if (! function_exists('format_price')) {
    /**
     * فرمت قیمت با جداکننده هزار متناسب با زبان جاری
     *
     * fa/ar → ۵٬۰۰۰٬۰۰۰
     * en/tr → 5,000,000
     *
     * Usage in Blade: {{ format_price(5000000) }}
     */
    function format_price($amount, ?string $locale = null): string
    {
        return NumberHelper::formatPrice($amount, $locale);
    }
}
