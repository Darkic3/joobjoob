<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\PromoBannerDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromoBannerRequest;
use App\Models\PromoBanner;
use App\Services\PromoBannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoBannerController extends Controller
{
    public function __construct(protected PromoBannerService $promoBannerService) {}

    public function index(Request $request): View
    {
        $banners = $this->promoBannerService->getOrderedBanners($request->all());

        return view('admin.promo-banners.index', compact('banners'));
    }

    public function create(): View
    {
        return view('admin.promo-banners.create');
    }

    public function store(PromoBannerRequest $request)
    {
        $dto = PromoBannerDTO::fromRequest($request);
        $this->promoBannerService->store($dto);

        return redirect()->route('admin.promo-banners.index')
            ->with('success', __('admin.promo_banners.created'));
    }

    public function edit(PromoBanner $promoBanner): View
    {
        return view('admin.promo-banners.edit', compact('promoBanner'));
    }

    public function update(PromoBannerRequest $request, PromoBanner $promoBanner)
    {
        $dto = PromoBannerDTO::fromRequest($request);
        $this->promoBannerService->update($promoBanner, $dto);

        return redirect()->route('admin.promo-banners.index')
            ->with('success', __('admin.promo_banners.updated'));
    }

    public function destroy(PromoBanner $promoBanner)
    {
        $this->promoBannerService->destroy($promoBanner);

        return redirect()->route('admin.promo-banners.index')
            ->with('success', __('admin.promo_banners.deleted'));
    }

    /**
     * ذخیرهٔ ترتیب درگ‌اندروپ (ایندکس) — همه بنرها با ترتیب جدید.
     */
    public function reorder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:promo_banners,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        if (count($ids) !== PromoBanner::count()) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => __('admin.promo_banners.order_invalid')], 422)
                : back()->with('error', __('admin.promo_banners.order_invalid'));
        }

        foreach ($ids as $index => $id) {
            PromoBanner::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        $this->promoBannerService->clearCache();

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('admin.promo_banners.order_saved')]);
        }

        return back()->with('success', __('admin.promo_banners.order_saved'));
    }
}
