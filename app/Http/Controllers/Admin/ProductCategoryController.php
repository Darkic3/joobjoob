<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductCategoryDTO;
use App\Helpers\ImageHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductCategoryRequest;
use App\Models\ProductCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    protected const UPLOAD_FOLDER = 'product-categories';

    public function index(Request $request): View
    {
        $categories = ProductCategory::query()
            ->withCount('products')
            ->with('parent')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim($request->string('search'));
                // name ستون JSON است — جستجو روی مقادیر ترجمه fa/en با JSON_EXTRACT
                $q->where(function ($w) use ($s) {
                    $w->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.fa')) LIKE ?", ["%{$s}%"])
                        ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$s}%"])
                        ->orWhere('slug', 'like', "%{$s}%");
                });
            })
            // ⚠️ به‌جای filled('is_active') — چون empty("0") در PHP true است و فیلتر «غیرفعال» بی‌اثر می‌شد
            ->when(in_array($request->input('is_active'), ['0', '1'], true), function ($q) use ($request) {
                $q->where('is_active', $request->input('is_active') === '1');
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parentCategories = ProductCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.product-categories.create', compact('parentCategories'));
    }

    public function store(ProductCategoryRequest $request): RedirectResponse
    {
        $dto = ProductCategoryDTO::fromRequest($request);

        $imagePath = null;
        if ($dto->getImage()?->isValid()) {
            // آپلود با هلپر اختصاصی شما (ImageHandler → ImageUploader → ImageResizer)
            $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER, $dto->getImage());
        }

        ProductCategory::create([
            'parent_id' => $dto->getParentId(),
            'name' => $dto->getName(),
            'slug' => $dto->getSlug(),
            'icon' => $dto->getIcon(),
            'image' => $imagePath,
            'description' => $dto->getDescription(),
            'meta_title' => $dto->getMetaTitle(),
            'meta_description' => $dto->getMetaDescription(),
            'sort_order' => $dto->getSortOrder(),
            'is_active' => $dto->getIsActive(),
        ]);

        return redirect()->route('admin.product-categories.index')
            ->with('success', __('admin.product_categories.created'));
    }

    public function edit(ProductCategory $productCategory): View
    {
        $parentCategories = ProductCategory::where('is_active', true)
            ->where('id', '!=', $productCategory->id)
            ->orderBy('sort_order')
            ->get();

        return view('admin.product-categories.edit', compact('productCategory', 'parentCategories'));
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory): RedirectResponse
    {
        $dto = ProductCategoryDTO::fromRequest($request);

        $imagePath = $productCategory->image;

        // حذف تصویر فعلی — وقتی کاربر از کامپوننت آپلود «حذف تصویر» را بزند
        if ($request->boolean('remove_image') && $productCategory->image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $productCategory->image);
            $imagePath = null;
        }

        if ($dto->getImage()?->isValid()) {
            // اول فایل جدید آپلود شود — اگر ناموفق بود تصویر قبلی پاک نمی‌شود (جلوگیری از از دست رفتن داده)
            $newPath = ImageHandler::handle(self::UPLOAD_FOLDER, $dto->getImage());
            if ($newPath) {
                if ($imagePath) {
                    ImageHandler::delete(self::UPLOAD_FOLDER, $imagePath);
                }
                $imagePath = $newPath;
            }
        }

        $productCategory->update([
            'parent_id' => $dto->getParentId(),
            'name' => $dto->getName(),
            'slug' => $dto->getSlug(),
            'icon' => $dto->getIcon(),
            'image' => $imagePath,
            'description' => $dto->getDescription(),
            'meta_title' => $dto->getMetaTitle(),
            'meta_description' => $dto->getMetaDescription(),
            'sort_order' => $dto->getSortOrder(),
            'is_active' => $dto->getIsActive(),
        ]);

        return redirect()->route('admin.product-categories.index')
            ->with('success', __('admin.product_categories.updated'));
    }

    public function destroy(ProductCategory $productCategory): RedirectResponse
    {
        // ⚠️ ستون product_category_id محصولات cascadeOnDelete است — حذف دسته = حذف محصولاتش!
        if ($productCategory->products()->count() > 0) {
            return back()
                ->with('error', __('admin.product_categories.has_products_error'));
        }

        // زیردسته‌ها هم نباید یتیم بمانند
        if ($productCategory->children()->count() > 0) {
            return back()
                ->with('error', __('admin.product_categories.has_children_error'));
        }

        if ($productCategory->image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $productCategory->image);
        }

        $productCategory->delete();

        return redirect()->route('admin.product-categories.index')
            ->with('success', __('admin.product_categories.deleted'));
    }
}
