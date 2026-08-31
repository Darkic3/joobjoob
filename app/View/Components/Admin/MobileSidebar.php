<?php

namespace App\View\Components\Admin;

use App\Models\ContactRequest;
use App\Models\Review;
use Closure;
use Illuminate\View\Component;
use Illuminate\View\View;

class MobileSidebar extends Component
{
    /** @var Closure(string): bool بررسی دسترسی کاربر جاری */
    public Closure $can;

    public bool $showProducts;

    public bool $showBlog;

    public bool $showContentDivider;

    public bool $showSettingsDivider;

    public string $productsTarget;

    public int $pendingReviewsCount = 0;

    public int $unreadContactsCount = 0;

    public function __construct()
    {
        $user = auth()->user();
        $this->can = fn (string $permission) => $user ? $user->canAccess($permission) : false;

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
            || ($this->can)('admin.languages.index');

        // در موبایل یک لینک برای کل گروه محصولات — به اولین بخش در دسترس برو
        $this->productsTarget = ($this->can)('admin.products.index')
            ? 'admin.products.index'
            : ((($this->can)('admin.brands.index')) ? 'admin.brands.index' : 'admin.specifications.index');

        // تعداد نظرات در انتظار تأیید
        if (($this->can)('admin.reviews.index')) {
            $this->pendingReviewsCount = Review::pending()->count();
        }

        // تعداد پیام‌های تماس خوانده‌نشده
        if (($this->can)('admin.contact.index')) {
            $this->unreadContactsCount = ContactRequest::unread()->count();
        }
    }

    public function render(): View
    {
        return view('components.admin.mobile-sidebar');
    }
}
