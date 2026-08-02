<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Helpers\ImageHandler;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected const UPLOAD_FOLDER = 'products';

    /**
     * ایجاد محصول جدید به همراه تصاویر
     */
    public function store(ProductDTO $dto): Product
    {
        // استفاده از Transaction برای اطمینان از یکپارچگی داده‌ها (محصول + تصاویر)
        return DB::transaction(function () use ($dto) {
            $data = $this->prepareData($dto);

            // 1. ایجاد خود محصول
            $product = Product::create($data);

            // 2. مدیریت و ذخیره تصاویر
            $this->handleImages($product, $dto->getImages());

            return $product;
        });
    }

    /**
     * به‌روزرسانی محصول و مدیریت تصاویر
     */
    public function update(Product $product, ProductDTO $dto): bool
    {
        return DB::transaction(function () use ($product, $dto) {
            $data = $this->prepareData($dto);

            // 3. مدیریت تصاویر در حالت ویرایش
            // نکته: در این پیاده‌سازی، اگر تصاویر جدیدی ارسال شود، تصاویر قدیمی حذف و تصاویر جدید جایگزین می‌شوند.
            // اگر می‌خواهید تصاویر جدید به قبلی‌ها اضافه شوند، منطق deleteOldImages را حذف کنید.
            if (!empty($dto->getImages())) {
                $this->deleteOldImages($product);
                $this->handleImages($product, $dto->getImages());
            }

            return $product->update($data);
        });
    }

    /**
     * حذف نرم محصول و پاکسازی فیزیکی تصاویر
     */
    public function destroy(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            $this->deleteOldImages($product);
            return $product->delete(); // به لطف SoftDeletes، حذف نرم انجام می‌شود
        });
    }

    /**
     * دریافت لیست صفحه‌بندی شده محصولات با قابلیت فیلتر
     */
    public function getPaginatedProducts(array $filters, int $perPage = 15)
    {
        return Product::query()
            ->with(['category', 'images']) // Eager Loading برای جلوگیری از N+1
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $search = $filters['search'];
                // جستجو در نام فارسی، انگلیسی و شناسه کالا (SKU)
                $q->where(function ($query) use ($search) {
                    $query->where('name->fa', 'like', "%{$search}%")
                        ->orWhere('name->en', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when(!empty($filters['category']), fn($q) => $q->where('product_category_id', $filters['category']))
            ->when(isset($filters['status']) && $filters['status'] !== '', fn($q) => $q->where('status', $filters['status']))
            ->latest()
            ->paginate($perPage);
    }

    /**
     * آماده‌سازی داده‌ها برای ذخیره در دیتابیس
     */
    private function prepareData(ProductDTO $dto): array
    {
        return [
            'product_category_id' => $dto->getProductCategoryId(),
            'name'                => $dto->getName(),
            'slug'                => $dto->getSlug(),
            'short_description'   => $dto->getShortDescription(),
            'description'         => $dto->getDescription(),
            'sku'                 => $dto->getSku(),
            'brand'               => $dto->getBrand(),
            'price'               => $dto->getPrice(),
            'discount_price'      => $dto->getDiscountPrice(),
            'stock'               => $dto->getStock(),
            'weight'              => $dto->getWeight(),
            'status'              => $dto->getStatus(),
            'is_featured'         => $dto->getIsFeatured(),
            'is_best_seller'      => $dto->getIsBestSeller(),
            'colors'              => $dto->getColors(),
            'sizes'               => $dto->getSizes(),
            'deal_ends_at'        => $dto->getDealEndsAt(),
        ];
    }

    /**
     * مدیریت آپلود چندگانه تصاویر و ایجاد رکوردهای ProductImage
     */
    private function handleImages(Product $product, array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $index => $file) {
            $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER, $file);

            $product->images()->create([
                'path'       => $imagePath,
                'is_primary' => $index === 0, // اولین تصویر به عنوان تصویر اصلی انتخاب می‌شود
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * حذف فیزیکی تمام تصاویر مرتبط با یک محصول
     */
    private function deleteOldImages(Product $product): void
    {
        $baseUpload = config('paths.upload', 'uploads');

        foreach ($product->images as $image) {
            $fullPath = public_path("{$baseUpload}/" . self::UPLOAD_FOLDER . "/{$image->path}");

            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }

            // حذف رکورد تصویر از دیتابیس
            $image->delete();
        }
    }
}
