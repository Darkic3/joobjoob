<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DiscountRequest;
use App\Models\Discount;
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
        return view('admin.discounts.create');
    }

    /**
     * ذخیره کد تخفیف جدید
     */
    public function store(DiscountRequest $request)
    {
        Discount::create($request->validated());

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

        return view('admin.discounts.show', compact('discount', 'recentUsage'));
    }

    /**
     * فرم ویرایش کد تخفیف
     */
    public function edit(Discount $discount)
    {
        return view('admin.discounts.edit', compact('discount'));
    }

    /**
     * به‌روزرسانی کد تخفیف
     */
    public function update(DiscountRequest $request, Discount $discount)
    {
        $discount->update($request->validated());

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

        return response()->json([
            'success' => true,
            'is_active' => $discount->is_active,
        ]);
    }
}
