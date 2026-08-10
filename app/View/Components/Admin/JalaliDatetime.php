<?php

namespace App\View\Components\Admin;

use App\Services\LocaleService;
use Carbon\Carbon;
use Illuminate\View\Component;
use Illuminate\View\View;

class JalaliDatetime extends Component
{
    public bool $isPersian;

    public string $inputId;

    public string $hiddenId;

    /** مقدار میلادی نرمال‌شده (Y-m-d H:i) برای ارسال به سرور */
    public string $greg = '';

    /** مقدار شمسی نمایشی (برای پیکر جلالی) */
    public string $jalaliInit = '';

    public function __construct(
        public string $name = '',
        public string $value = '',
        public string $label = '',
        public bool $required = false,
        public string $placeholder = '۱۴۰۳/۰۵/۱۴ ۱۵:۳۰',
        ?LocaleService $localeService = null,
    ) {
        $localeService ??= app(LocaleService::class);
        $this->isPersian = $localeService->isJalali(app()->getLocale());
        $this->inputId = 'jdp-'.preg_replace('/[^a-zA-Z0-9_-]/', '-', $name).'-'.uniqid();
        $this->hiddenId = $this->inputId.'-hidden';

        if ($value !== '') {
            try {
                $this->greg = Carbon::parse($value)->format('Y-m-d H:i');
                // تبدیل به شمسی با ارقام انگلیسی (برای پارس در JS)
                $this->jalaliInit = jdate_format('Y/m/d H:i', strtotime($this->greg), '', 'Asia/Tehran', 'en');
            } catch (\Throwable) {
                // مقدار نامعتبر → خالی
            }
        }
    }

    public function render(): View
    {
        return view('components.admin.jalali-datetime');
    }
}
