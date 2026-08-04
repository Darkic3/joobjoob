<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\ProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Specification;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): View
    {
        // دریافت دسته‌بندی‌های فعال برای استفاده در فیلترهای صفحه ایندکس
        $categories = ProductCategory::where('is_active', true)->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        // دریافت محصولات صفحه‌بندی شده با حفظ فیلترها
        $products = $this->productService->getPaginatedProducts($request->all());
        $products->appends($request->query());

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create(): View
    {
        $categories = ProductCategory::where('is_active', true)->orderByDesc('id')->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        // مشخصه‌های والد (گروه‌ها) به همراه فرزندان — برای انتخابگر واریانت
        $specifications = Specification::whereNull('parent_id')->where('active', true)->with('children')->orderBy('id')->get();
        $textSpecifications = Specification::where('type', 'text')->whereNull('parent_id')->where('active', true)->orderBy('id')->get();

        // همه محصولات برای انتخابگر محصولات مرتبط/مکمل
        $products = Product::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']);

        return view('admin.products.create', compact('categories', 'brands', 'specifications', 'textSpecifications', 'products'));
    }

    public function store(ProductRequest $request)
    {
        // تبدیل درخواست به DTO
        $productDTO = ProductDTO::fromRequest($request);

        // ذخیره از طریق سرویس
        $this->productService->store($productDTO);

        return redirect()->route('admin.products.index')
            ->with('success', 'محصول با موفقیت ایجاد شد.');
    }

    public function edit(Product $product): View
    {
        $product->load([
            'images',
            'brand',
            'productSpecifications.specification',
            'productSpecifications.children.specification',
            'variants.specifications',
            'properties',
            'related',
            'complement',
        ]);

        $categories = ProductCategory::where('is_active', true)->orderByDesc('id')->get();
        $brands = Brand::where('active', true)->orderBy('id')->get();

        $specifications = Specification::whereNull('parent_id')->where('active', true)->with('children')->orderBy('id')->get();
        $textSpecifications = Specification::where('type', 'text')->whereNull('parent_id')->where('active', true)->orderBy('id')->get();

        $products = Product::where('is_active', true)->orderBy('id')->get(['id', 'name', 'slug']);

        return view('admin.products.edit', compact('product', 'categories', 'brands', 'specifications', 'textSpecifications', 'products'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $productDTO = ProductDTO::fromRequest($request);

        $this->productService->update($product, $productDTO);

        return redirect()->route('admin.products.index')
            ->with('success', 'محصول با موفقیت به‌روزرسانی شد.');
    }

    public function destroy(Product $product)
    {
        $this->productService->destroy($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'محصول با موفقیت حذف شد.');
    }
}
