<?php

namespace App\View\Composers;

use App\Services\TrustBadgeService;
use Illuminate\View\View;

/**
 * داده‌های اختصاصی فوتر سایت — نشان‌های اعتماد (اینماد، درگاه پرداخت و…).
 *
 * منطق آماده‌سازی از ویو خارج شده؛ سرویس، کش و فیلتر محل نمایش (placement)
 * را مدیریت می‌کند و ویو فقط مصرف‌کننده است.
 */
class FooterComposer
{
    public function __construct(protected TrustBadgeService $trustBadges) {}

    public function compose(View $view): void
    {
        $view->with('footerTrustBadges', $this->trustBadges->getActiveForFooter());
    }
}
