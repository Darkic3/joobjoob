<?php

namespace App\View\Components\Admin;

use App\Models\ContactRequest;
use App\Models\Order;
use App\Models\Review;
use Closure;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sidebar extends Component
{
    /** @var Closure(string): bool بررسی دسترسی کاربر جاری */
    public Closure $can;

    public bool $productsOpen;

    public bool $blogOpen;

    public bool $showProducts;

    public bool $showBlog;

    public bool $showContentDivider;

    public bool $showSettingsDivider;

    public int $pendingReviewsCount = 0;

    public int $newOrdersCount = 0;

    public int $unreadContactsCount = 0;

    public function __construct()
    {
        $user = auth()->user();
        $this->can = fn (string $permission) => $user ? $user->canAccess($permission) : false;

        $this->productsOpen = request()->routeIs('admin.products.*')
            || request()->routeIs('admin.brands.*')
            || request()->routeIs('admin.specifications.*');
        $this->blogOpen = request()->routeIs('admin.blogs.*')
            || request()->routeIs('admin.blog-categories.*');

        $this->showProducts = ($this->can)('admin.products.index')
            || ($this->can)('admin.brands.index')
            || ($this->can)('admin.specifications.index');
        $this->showBlog = ($this->can)('admin.blogs.index')
            || ($this->can)('admin.blog-categories.index');
        $this->showContentDivider = ($this->can)('admin.hero-slides.index')
            || $this->showProducts
            || ($this->can)('admin.product-categories.index')
            || $this->showBlog
            || ($this->can)('admin.trust-badges.index')
            || ($this->can)('admin.promo-banners.index');
        $this->showSettingsDivider = ($this->can)('admin.settings.edit')
            || ($this->can)('admin.roles.index')
            || ($this->can)('admin.users.index')
            || ($this->can)('admin.languages.index')
            || ($this->can)('admin.shipping-methods.index')
            || ($this->can)('admin.banks.index');

        // تعداد نظرات در انتظار تأیید
        if (($this->can)('admin.reviews.index')) {
            $this->pendingReviewsCount = Review::pending()->count();
        }

        // تعداد سفارشات جدید (در انتظار تأیید فیش یا پرداخت)
        if (($this->can)('admin.orders.index')) {
            $this->newOrdersCount = Order::whereIn('order_status', [
                'wait_for_verification',
                'pending_confirmation',
            ])->count();
        }

        // تعداد پیام‌های تماس خوانده‌نشده
        if (($this->can)('admin.contact.index')) {
            $this->unreadContactsCount = ContactRequest::unread()->count();
        }
    }

    public function render(): View
    {
        return view('components.admin.sidebar');
    }
}
