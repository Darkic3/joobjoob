<?php

namespace App\View\Components\Admin;

use App\Models\ContactRequest;
use App\Models\Order;
use App\Models\Review;
use App\Models\SystemError;
use Closure;
use Illuminate\View\Component;
use Illuminate\View\View;

class Sidebar extends Component
{
    /** @var Closure(string): bool */
    public Closure $can;

    public bool $productsOpen;

    public bool $blogOpen;

    public bool $showProducts;

    public bool $showBlog;

    public bool $showContentDivider;

    public bool $showSettingsDivider;

    public bool $showAuditLogs;

    public int $pendingReviewsCount = 0;

    public int $newOrdersCount = 0;

    public int $unreadContactsCount = 0;

    public int $unresolvedErrorsCount = 0;

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
            || ($this->can)('admin.shipping-methods.index')
            || ($this->can)('admin.banks.index');
        $this->showAuditLogs = ($this->can)('admin.audit-logs.index');

        if (($this->can)('admin.reviews.index')) {
            $this->pendingReviewsCount = Review::pending()->count();
        }

        if (($this->can)('admin.orders.index')) {
            $this->newOrdersCount = Order::whereIn('order_status', [
                'wait_for_verification',
                'pending_confirmation',
            ])->count();
        }

        if (($this->can)('admin.contact.index')) {
            $this->unreadContactsCount = ContactRequest::unread()->count();
        }

        // تعداد خطاهای حل‌نشده سیستمی
        try {
            $this->unresolvedErrorsCount = SystemError::unresolved()->count();
        } catch (\Throwable $e) {
            $this->unresolvedErrorsCount = 0;
        }
    }

    public function render(): View
    {
        return view('components.admin.sidebar');
    }
}