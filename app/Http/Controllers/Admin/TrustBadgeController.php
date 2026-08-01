<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\TrustBadgeDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TrustBadgeRequest;
use App\Models\TrustBadge;
use App\Services\TrustBadgeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrustBadgeController extends Controller
{
    public function __construct(protected TrustBadgeService $trustBadgeService) {}

    public function index(Request $request): View
    {
        $badges = $this->trustBadgeService->getPaginatedBadges($request->all());
        $badges->appends($request->query()); // حفظ فیلترها در صفحه‌بندی

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
}
