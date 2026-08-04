<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BrandDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BrandRequest;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function __construct(protected BrandService $brandService) {}

    public function index(Request $request): View
    {
        $brands = $this->brandService->getPaginatedBrands($request->all());
        $brands->appends($request->query());

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
}
