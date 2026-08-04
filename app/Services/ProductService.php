<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Helpers\ImageHandler;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected const UPLOAD_FOLDER = 'products';

    // ابعاد رسیز شده برای هر تصویر (ImageResizer با cover-crop)
    protected const IMAGE_SIZES = [
        'thumb'  => [300, 300],
        'medium' => [800, 800],
        'large'  => [1200, 1200],
    ];

    /**
     * ایجاد محصول جدید به همراه تصاویر
     */
    public function store(ProductDTO $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $data = $this->prepareData($dto);

            $product = Product::create($data);

            // آپلود تصاویر جدید
            $newImages = $this->appendImages($product, $dto->getImages());

            // تعیین تصویر اصلی
            $this->setMainImage($product, $dto->getMainImageId(), $dto->getMainNewIndex(), $newImages);

            return $product;
        });
    }

    /**
     * به‌روزرسانی محصول + مدیریت گالری
     * - تصاویر قبلی حذف نمی‌شوند مگر اینکه در delete_images آمده باشند
     * - تصاویر جدید به گالری اضافه می‌شوند
     */
    public function update(Product $product, ProductDTO $dto): bool
    {
        return DB::transaction(function () use ($product, $dto) {
            $data = $this->prepareData($dto);

            $product->update($data);

            // حذف تصاویر انتخابی توسط کاربر
            if (!empty($dto->getDeleteImages())) {
                $this->deleteImagesByIds($product, $dto->getDeleteImages());
            }

            // افزودن تصاویر جدید (بدون حذف قبلی‌ها)
            $newImages = $this->appendImages($product, $dto->getImages());

            // تعیین تصویر اصلی
            $this->setMainImage($product, $dto->getMainImageId(), $dto->getMainNewIndex(), $newImages);

            return true;
        });
    }

    /**
     * حذف نرم محصول و پاکسازی فیزیکی تصاویر
     */
    public function destroy(Product $product): bool
    {
        return DB::transaction(function () use ($product) {
            $this->deleteOldImages($product);
            return $product->delete();
        });
    }

    /**
     * دریافت لیست صفحه‌بندی شده محصولات با قابلیت فیلتر پیشرفته
     */
    public function getPaginatedProducts(array $filters, int $perPage = 15)
    {
        $query = Product::query()
            ->with(['category', 'images']); // Eager Loading برای جلوگیری از N+1

        // جستجو در نام فارسی/انگلیسی، برند، اسلاگ و شناسه کالا (SKU)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name->fa', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // فیلتر دسته‌بندی
        $query->when(!empty($filters['category']), fn($q) => $q->where('product_category_id', $filters['category']));

        // فیلتر وضعیت نمایش (فعال/غیرفعال)
        $query->when(($filters['status'] ?? '') === '1', fn($q) => $q->where('is_active', true));
        $query->when(($filters['status'] ?? '') === '0', fn($q) => $q->where('is_active', false));

        // فیلتر موجودی انبار
        $query->when(($filters['availability'] ?? '') === 'in_stock', fn($q) => $q->where('stock', '>', 0));
        $query->when(($filters['availability'] ?? '') === 'out_of_stock', fn($q) => $q->where('stock', '<=', 0));

        // فیلتر بازه قیمت
        $query->when(isset($filters['price_min']) && $filters['price_min'] !== '', fn($q) => $q->where('price', '>=', (int) $filters['price_min']));
        $query->when(isset($filters['price_max']) && $filters['price_max'] !== '', fn($q) => $q->where('price', '<=', (int) $filters['price_max']));

        // فیلتر محصولات ویژه و پرفروش
        $query->when(!empty($filters['featured']), fn($q) => $q->where('is_featured', true));
        $query->when(!empty($filters['best_seller']), fn($q) => $q->where('is_best_seller', true));

        // مرتب‌سازی
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name->en');
                break;
            case 'name_desc':
                $query->orderByDesc('name->en');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default: // newest
                $query->latest();
                break;
        }

        return $query->paginate($perPage);
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
            'is_active'           => $dto->getIsActive(),
            'is_featured'         => $dto->getIsFeatured(),
            'is_best_seller'      => $dto->getIsBestSeller(),
            'colors'              => $dto->getColors(),
            'sizes'               => $dto->getSizes(),
            'deal_ends_at'        => $dto->getDealEndsAt(),
        ];
    }

    /**
     * افزودن تصاویر جدید به گالری (بدون حذف تصاویر قبلی)
     * @return array<int, ProductImage> ایندکس‌شده بر اساس ایندکس فایل ارسالی
     */
    private function appendImages(Product $product, array $uploadedFiles): array
    {
        $created = [];
        $startSort = (int) $product->images()->max('sort_order');

        foreach ($uploadedFiles as $index => $file) {
            $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER, $file, self::IMAGE_SIZES);

            if (!$imagePath) {
                continue;
            }

            $created[$index] = $product->images()->create([
                'path'       => $imagePath,
                'is_primary' => false,
                'sort_order' => $startSort + $index + 1,
            ]);
        }

        return $created;
    }

    /**
     * حذف فیزیکی و رکوردی چند تصویر مشخص
     */
    private function deleteImagesByIds(Product $product, array $ids): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return;
        }

        $images = $product->images()->whereIn('id', $ids)->get();

        foreach ($images as $image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $image->path, self::IMAGE_SIZES);
            $image->delete();
        }
    }

    /**
     * تعیین تصویر اصلی محصول
     * اولویت: main_image_id (تصویر موجود) → main_new_index (تصویر جدید) → اولین تصویر گالری
     */
    private function setMainImage(Product $product, ?int $mainImageId, ?int $mainNewIndex, array $newImages): void
    {
        $target = null;

        // ۱) تصویر اصلی از بین تصاویر موجود
        if ($mainImageId) {
            $target = $product->images()->whereKey($mainImageId)->first();
        }

        // ۲) تصویر اصلی از بین تصاویر جدید ارسالی
        if (!$target && $mainNewIndex !== null && isset($newImages[$mainNewIndex])) {
            $target = $newImages[$mainNewIndex];
        }

        // ۳) در غیر این صورت اولین تصویر گالری
        if (!$target) {
            $target = $product->images()->orderBy('sort_order')->first();
        }

        if (!$target) {
            // هیچ تصویری وجود ندارد → تصویر اصلی پاک می‌شود
            $product->update(['main_image' => null]);
            return;
        }

        // فقط یک تصویر اصلی باشد
        $product->images()->update(['is_primary' => false]);
        $target->update(['is_primary' => true]);

        // ذخیره در ستون main_image جدول products
        $product->update(['main_image' => $target->path]);
    }

    /**
     * حذف فیزیکی تمام تصاویر مرتبط با یک محصول (هنگام حذف محصول)
     */
    private function deleteOldImages(Product $product): void
    {
        foreach ($product->images as $image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $image->path, self::IMAGE_SIZES);
            $image->delete();
        }
    }
}
