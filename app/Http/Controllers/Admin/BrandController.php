<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BrandDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandService) {}

    public function index(Request $request): View
    {
        $brands = $this->brandService->getOrderedBrands($request->all());

        return view('admin.brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->brandService->store($dto);

        return redirect()->route('admin.brands.index')
            ->with('success', 'برند با موفقیت ایجاد شد.');
    }

    public function edit(Brand $brand): View
    {
        return view('admin.brands.edit', compact('brand'));
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $dto = BrandDTO::fromRequest($request);
        $this->brandService->update($brand, $dto);

        return redirect()->route('admin.brands.index')
            ->with('success', 'برند با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Brand $brand)
    {
        $this->brandService->destroy($brand);

        return redirect()->route('admin.brands.index')
            ->with('success', 'برند با موفقیت حذف شد.');
    }

    /**
     * ذخیرهٔ ترتیب درگ‌اندروپ (ایندکس) — همه برندها با ترتیب جدید.
     */
    public function reorder(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:brands,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        if (count($ids) !== Brand::count()) {
            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => __('admin.brands.order_invalid')], 422)
                : back()->with('error', __('admin.brands.order_invalid'));
        }

        foreach ($ids as $index => $id) {
            Brand::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => __('admin.brands.order_saved')]);
        }

        return back()->with('success', __('admin.brands.order_saved'));
    }
}
