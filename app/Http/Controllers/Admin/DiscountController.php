<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DiscountRequest;
use App\Models\Brand;
use App\Models\Discount;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    /**
     * لیست کدهای تخفیف
     */
    public function index(Request $request)
    {
        $query = Discount::withCount('usageRecords');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $discounts = $query->latest()->paginate(15)->withQueryString();

        return view('admin.discounts.index', compact('discounts'));
    }

    /**
     * فرم ایجاد کد تخفیف جدید
     */
    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        $brands = Brand::orderBy('id')->get();
        $products = Product::orderBy('name')->get();
        $users = \App\Models\User::select('id', 'name', 'mobile', 'email')->orderBy('name')->get();

        return view('admin.discounts.create', compact('categories', 'brands', 'products', 'users'));
    }

    /**
     * ذخیره کد تخفیف جدید
     */
    public function store(DiscountRequest $request)
    {
        $discount = Discount::create($request->validated());

        $this->syncDiscountable($discount, $request);
        $this->syncUsers($discount, $request);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'کد تخفیف با موفقیت ایجاد شد.');
    }

    /**
     * نمایش جزئیات کد تخفیف
     */
    public function show(Discount $discount)
    {
        $discount->loadCount('usageRecords');
        $recentUsage = $discount->usageRecords()
            ->with('user:id,name,email')
            ->latest()
            ->take(20)
            ->get();

        // بارگذاری روابط discountable
        $discount->load(['categories:id,name', 'brands:id,title', 'products:id,name']);

        return view('admin.discounts.show', compact('discount', 'recentUsage'));
    }

    /**
     * فرم ویرایش کد تخفیف
     */
    public function edit(Discount $discount)
    {
        $discount->load(['categories:id', 'brands:id', 'products:id', 'users:id']);

        $categories = ProductCategory::orderBy('name')->get();
        $brands = Brand::orderBy('id')->get();
        $products = Product::orderBy('name')->get();
        $users = \App\Models\User::select('id', 'name', 'mobile', 'email')->orderBy('name')->get();

        return view('admin.discounts.edit', compact('discount', 'categories', 'brands', 'products', 'users'));
    }

    /**
     * به‌روزرسانی کد تخفیف
     */
    public function update(DiscountRequest $request, Discount $discount)
    {
        $discount->update($request->validated());

        // پاکسازی روابط قبلی و ذخیره جدید
        $discount->categories()->detach();
        $discount->brands()->detach();
        $discount->products()->detach();
        $this->syncDiscountable($discount, $request);
        $this->syncUsers($discount, $request);

        return redirect()->route('admin.discounts.index')
            ->with('success', 'کد تخفیف با موفقیت به‌روزرسانی شد.');
    }

    /**
     * حذف کد تخفیف (soft delete)
     */
    public function destroy(Discount $discount)
    {
        $discount->delete();

        return redirect()->route('admin.discounts.index')
            ->with('success', 'کد تخفیف حذف شد.');
    }

    /**
     * تغییر وضعیت فعال/غیرفعال (AJAX)
     */
    public function toggleStatus(Discount $discount)
    {
        $discount->update(['is_active' => ! $discount->is_active]);

        return back()->with('success', 'وضعیت تغییر کرد');
    }

    /**
     * همگام‌سازی روابط polymorphic discountable
     */
    private function syncDiscountable(Discount $discount, DiscountRequest $request): void
    {
        $payType = $request->input('pay_type');
        $ids = $request->input('discountable_ids', []);

        if (empty($payType) || empty($ids)) {
            return;
        }

        match ($payType) {
            'category' => $discount->categories()->syncWithoutDetaching($ids),
            'brand' => $discount->brands()->syncWithoutDetaching($ids),
            'product' => $discount->products()->syncWithoutDetaching($ids),
            default => null,
        };
    }

    private function syncUsers(Discount $discount, DiscountRequest $request): void
    {
        $userIds = $request->input('user_ids', []);
        $discount->users()->sync($userIds);
    }

    /**
     * فیلتر هوشمند کاربران بر اساس معیارهای مختلف (AJAX)
     */
    public function filterUsers(Request $request)
    {
        $filter = $request->get('filter');

        $query = \App\Models\User::select('id', 'name', 'mobile', 'email');

        switch ($filter) {
            case 'new_month':
                // کاربرانی که در یک ماه اخیر ثبت نام کرده‌اند
                $query->where('created_at', '>=', now()->subMonth());
                break;

            case 'new_week':
                // کاربرانی که در یک هفته اخیر ثبت نام کرده‌اند
                $query->where('created_at', '>=', now()->subWeek());
                break;

            case 'no_orders':
                // کاربرانی که تا به حال سفارشی نداشته‌اند
                $query->whereDoesntHave('orders');
                break;

            case 'has_orders':
                // کاربرانی که حداقل یک سفارش داشته‌اند
                $query->has('orders');
                break;

            case 'no_paid_orders':
                // کاربرانی که سفارش پرداخت شده ندارند (خرید اولی‌ها)
                $query->whereDoesntHave('orders', function ($q) {
                    $q->where('order_status', 'paid');
                });
                break;

            case 'inactive_30d':
                // کاربرانی که ۳۰ روز است فعالیت نداشته‌اند
                $query->where('updated_at', '<=', now()->subDays(30));
                break;

            case 'active_7d':
                // کاربرانی که در ۷ روز اخیر فعال بوده‌اند
                $query->where('updated_at', '>=', now()->subWeek());
                break;

            default:
                // بدون فیلتر — همه کاربران
                break;
        }

        $users = $query->orderBy('name')->limit(500)->get();

        return response()->json(['users' => $users]);
    }
}
