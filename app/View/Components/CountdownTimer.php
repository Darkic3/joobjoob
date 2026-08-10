<?php

namespace App\View\Components;

use DateTimeInterface;
use Illuminate\View\Component;
use Illuminate\View\View;

class CountdownTimer extends Component
{
    /** @var int|null زمان پایان به میلی‌ثانیه (برای JS) */
    public ?int $endMs = null;

    public function __construct(
        public DateTimeInterface|int|string|null $endsAt = null,
        public ?int $endsInSeconds = null,
        public string $id = 'cd',
    ) {
        $this->endMs = $this->resolveEndMs($endsAt);
    }

    public function render(): View
    {
        return view('components.countdown-timer');
    }

    private function resolveEndMs(DateTimeInterface|int|string|null $endsAt): ?int
    {
        if ($endsAt === null || $endsAt === '') {
            return null;
        }

        if ($endsAt instanceof DateTimeInterface) {
            return $endsAt->getTimestamp() * 1000;
        }

        if (is_numeric($endsAt)) {
            $ms = (int) $endsAt;

            // اگر ورودی به ثانیه بود (نه میلی‌ثانیه) به میلی‌ثانیه تبدیل کن
            return $ms < 100000000000 ? $ms * 1000 : $ms;
        }

        // رشتهٔ قابل پارس (مثل datetime از دیتابیس)
        return strtotime($endsAt) !== false ? strtotime($endsAt) * 1000 : null;
    }
}
