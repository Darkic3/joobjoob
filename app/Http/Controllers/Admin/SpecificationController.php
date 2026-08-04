<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\SpecificationDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpecificationRequest;
use App\Models\Specification;
use App\Services\SpecificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpecificationController extends Controller
{
    public function __construct(protected SpecificationService $specificationService) {}

    public function index(Request $request): View
    {
        $specifications = $this->specificationService->getPaginatedSpecifications($request->all());
        $specifications->appends($request->query());

        return view('admin.specifications.index', compact('specifications'));
    }

    public function create(): View
    {
        $parents = Specification::whereNull('parent_id')->where('active', true)->orderBy('id')->get();

        return view('admin.specifications.create', compact('parents'));
    }

    public function store(SpecificationRequest $request)
    {
        $dto = SpecificationDTO::fromRequest($request);
        $this->specificationService->store($dto);

        return redirect()->route('admin.specifications.index')
            ->with('success', 'مشخصه با موفقیت ایجاد شد.');
    }

    public function edit(Specification $specification): View
    {
        $parents = Specification::whereNull('parent_id')
            ->where('active', true)
            ->where('id', '!=', $specification->id)
            ->orderBy('id')
            ->get();

        return view('admin.specifications.edit', compact('specification', 'parents'));
    }

    public function update(SpecificationRequest $request, Specification $specification)
    {
        $dto = SpecificationDTO::fromRequest($request);
        $this->specificationService->update($specification, $dto);

        return redirect()->route('admin.specifications.index')
            ->with('success', 'مشخصه با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Specification $specification)
    {
        $this->specificationService->destroy($specification);

        return redirect()->route('admin.specifications.index')
            ->with('success', 'مشخصه با موفقیت حذف شد.');
    }
}
