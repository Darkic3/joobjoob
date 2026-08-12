<?php

namespace App\View\Composers;

use App\Services\CartService;
use App\Services\WishlistService;
use Illuminate\View\View;

/**
 * داده‌های اختصاصی چیدمان سایت (نوار اعلان + شمارندهٔ سبد) — منطق آماده‌سازی از ویو خارج شده.
 */
class SiteLayoutComposer
{
    public function __construct(
        protected CartService $cart,
        protected WishlistService $wishlist,
    ) {}

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
            // شمارندهٔ واقعی سبد و علاقه‌مندی‌ها (هدر + نوار پایین موبایل)
            'cartCount' => $this->cart->count(),
            'wishlistCount' => $this->wishlist->count(),
        ]);
    }
}
