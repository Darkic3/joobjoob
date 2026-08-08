<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\TrustBadgeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrustBadgeRequest;
use App\Models\TrustBadge;
use App\Services\TrustBadgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrustBadgeController extends Controller
{
    public function __construct(protected TrustBadgeService $trustBadgeService) {}

    public function index(Request $request): View
    {
        $badges = $this->trustBadgeService->getOrderedBadges($request->all());

        return view('admin.trust-badges.index', compact('badges'));
    }

    public function create(): View
    {
        return view('admin.trust-badges.create');
    }

    public function store(TrustBadgeRequest $request)
    {
        $dto = TrustBadgeDTO::fromRequest($request);
        $this->trustBadgeService->store($dto);

        return redirect()->route('admin.trust-badges.index')
            ->with('success', 'نشان اعتماد با موفقیت ایجاد شد.');
    }

    public function edit(TrustBadge $trustBadge): View
    {
        return view('admin.trust-badges.edit', compact('trustBadge'));
    }

    public function update(TrustBadgeRequest $request, TrustBadge $trustBadge)
    {
        $dto = TrustBadgeDTO::fromRequest($request);
        $this->trustBadgeService->update($trustBadge, $dto);

        return redirect()->route('admin.trust-badges.index')
            ->with('success', 'نشان اعتماد با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(TrustBadge $trustBadge)
    {
        $this->trustBadgeService->destroy($trustBadge);

        return redirect()->route('admin.trust-badges.index')
            ->with('success', 'نشان اعتماد با موفقیت حذف شد.');
    }

    /**
     * ذخیرهٔ ترتیب درگ‌اندروپ (ایندکس) — همه نشان‌ها با ترتیب جدید.
     */
    public function reorder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:trust_badges,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        if (count($ids) !== TrustBadge::count()) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => __('admin.trust_badges.order_invalid')], 422)
                : back()->with('error', __('admin.trust_badges.order_invalid'));
        }

        foreach ($ids as $index => $id) {
            TrustBadge::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        $this->trustBadgeService->clearCache();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('admin.trust_badges.order_saved')]);
        }

        return back()->with('success', __('admin.trust_badges.order_saved'));
    }
}
