<?php

namespace App\View\Composers;

use Illuminate\View\View;

/**
 * داده‌های اختصاصی چیدمان سایت (نوار اعلان) — منطق آماده‌سازی از ویو خارج شده.
 */
class SiteLayoutComposer
{
    public function compose(View $view): void
    {
        $announceMessages = array_values(array_filter([
            setting('announce_1'),
            setting('announce_2'),
            setting('announce_3'),
        ]));

        $view->with([
            'announceMessages' => $announceMessages,
            'firstAnnounce' => $announceMessages[0] ?? '🚚 '.__('messages.trust_free_shipping_desc'),
        ]);
    }
}
