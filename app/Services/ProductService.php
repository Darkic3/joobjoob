<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Helpers\ImageHandler;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductSpecification;
use App\Models\ProductVariant;
use App\Models\Property;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected const UPLOAD_FOLDER = 'products';

    // ابعاد رسیز شده برای هر تصویر (ImageResizer با cover-crop)
    protected const IMAGE_SIZES = [
        'thumb' => [300, 300],
        'medium' => [800, 800],
        'large' => [1200, 1200],
    ];

    /**
     * ایجاد محصول جدید به همراه تصاویر، مشخصات، واریانت‌ها و ویژگی‌ها
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

            // مشخصات، واریانت‌ها، ویژگی‌ها و ارتباطات
            $this->syncSpecifications($product, $dto);
            $this->syncVariants($product, $dto);
            $this->syncProperties($product, $dto);
            $this->syncAssociations($product, $dto);

            return $product;
        });
    }

    /**
     * به‌روزرسانی محصول + مدیریت گالری، مشخصات، واریانت‌ها و ویژگی‌ها
     */
    public function update(Product $product, ProductDTO $dto): bool
    {
        return DB::transaction(function () use ($product, $dto) {
            $data = $this->prepareData($dto);

            $product->update($data);

            // حذف تصاویر انتخابی توسط کاربر
            if (! empty($dto->getDeleteImages())) {
                $this->deleteImagesByIds($product, $dto->getDeleteImages());
            }

            // افزودن تصاویر جدید (بدون حذف قبلی‌ها)
            $newImages = $this->appendImages($product, $dto->getImages());

            // تعیین تصویر اصلی
            $this->setMainImage($product, $dto->getMainImageId(), $dto->getMainNewIndex(), $newImages);

            // مشخصات، واریانت‌ها، ویژگی‌ها و ارتباطات
            $this->syncSpecifications($product, $dto);
            $this->syncVariants($product, $dto);
            $this->syncProperties($product, $dto);
            $this->syncAssociations($product, $dto);

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
            ->with(['category', 'images', 'brand']); // Eager Loading برای جلوگیری از N+1

        // جستجو در نام فارسی/انگلیسی، برند، اسلاگ و شناسه کالا (SKU)
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name->fa', 'like', "%{$search}%")
                    ->orWhere('name->en', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('brand', fn ($b) => $b->where('title->fa', 'like', "%{$search}%"));
            });
        }

        // فیلتر دسته‌بندی
        $query->when(! empty($filters['category']), fn ($q) => $q->where('product_category_id', $filters['category']));

        // فیلتر برند
        $query->when(! empty($filters['brand_id']), fn ($q) => $q->where('brand_id', $filters['brand_id']));

        // فیلتر وضعیت نمایش (فعال/غیرفعال)
        $query->when(($filters['status'] ?? '') === '1', fn ($q) => $q->where('is_active', true));
        $query->when(($filters['status'] ?? '') === '0', fn ($q) => $q->where('is_active', false));

        // فیلتر موجودی انبار
        $query->when(($filters['availability'] ?? '') === 'in_stock', fn ($q) => $q->where('stock', '>', 0));
        $query->when(($filters['availability'] ?? '') === 'out_of_stock', fn ($q) => $q->where('stock', '<=', 0));

        // فیلتر بازه قیمت (بر اساس final_price طبق گزارش)
        $query->when(isset($filters['price_min']) && $filters['price_min'] !== '', fn ($q) => $q->where('final_price', '>=', (int) $filters['price_min']));
        $query->when(isset($filters['price_max']) && $filters['price_max'] !== '', fn ($q) => $q->where('final_price', '<=', (int) $filters['price_max']));

        // فیلتر محصولات ویژه و پرفروش
        $query->when(! empty($filters['featured']), fn ($q) => $q->where('is_featured', true));
        $query->when(! empty($filters['best_seller']), fn ($q) => $q->where('is_best_seller', true));

        // مرتب‌سازی
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('final_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('final_price', 'desc');
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
        $finalPrice = $dto->getDiscountPrice() && $dto->getDiscountPrice() > 0
            ? $dto->getDiscountPrice()
            : $dto->getPrice();

        return [
            'product_category_id' => $dto->getProductCategoryId(),
            'brand_id' => $dto->getBrandId(),
            'name' => $dto->getName(),
            'slug' => $dto->getSlug(),
            'short_description' => $dto->getShortDescription(),
            'description' => $dto->getDescription(),
            'sku' => $dto->getSku(),
            'price' => $dto->getPrice(),
            'discount_price' => $dto->getDiscountPrice(),
            'final_price' => $finalPrice,
            'stock' => $dto->getStock(),
            'weight' => $dto->getWeight(),
            'is_active' => $dto->getIsActive(),
            'is_featured' => $dto->getIsFeatured(),
            'is_best_seller' => $dto->getIsBestSeller(),
            'deal_ends_at' => $dto->getDealEndsAt(),
            'timer_active' => $dto->getTimerActive(),
            'start_timer' => $dto->getStartTimer(),
            'end_timer' => $dto->getEndTimer(),
        ];
    }

    // ==========================================
    // مشخصات (Specifications)
    // ==========================================

    /**
     * همگام‌سازی مشخصات محصول
     * - مشخصه‌های انتخابی (select): ردیف والد (value=NULL) + ردیف فرزند برای هر مقدار
     * - مقادیر نوشتاری (text): ردیف با value=متن
     */
    private function syncSpecifications(Product $product, ProductDTO $dto): void
    {
        // حذف نرم ردیف‌های قبلی
        ProductSpecification::where('product_id', $product->id)->delete();

        $rows = [];

        // مشخصه‌های انتخابی: [parent_id => [child_id, ...]]
        foreach ($dto->getSpecs() as $parentId => $childIds) {
            $rows[] = [
                'specification_id' => $parentId,
                'value' => null,
                'parent_id' => null,
            ];
        }

        // مقادیر نوشتاری: [specification_id => value]
        foreach ($dto->getTextSpecs() as $specId => $value) {
            $rows[] = [
                'specification_id' => $specId,
                'value' => $value,
                'parent_id' => null,
            ];
        }

        // ساخت ردیف‌های والد و نگه‌داشتن نگاشت specification_id → ردیف
        $parentRows = [];
        foreach ($rows as $row) {
            $ps = ProductSpecification::create(array_merge($row, ['product_id' => $product->id]));
            if ($row['value'] === null) {
                $parentRows[$row['specification_id']] = $ps->id;
            }
        }

        // ردیف‌های فرزند (مقادیر انتخابی) — به ردیف والد خود متصل می‌شوند
        foreach ($dto->getSpecs() as $parentId => $childIds) {
            $parentRowId = $parentRows[$parentId] ?? null;
            if (! $parentRowId) {
                continue;
            }

            foreach ($childIds as $childId) {
                ProductSpecification::create([
                    'product_id' => $product->id,
                    'specification_id' => $childId,
                    'value' => null,
                    'parent_id' => $parentRowId,
                ]);
            }
        }
    }

    // ==========================================
    // واریانت‌ها (Variants) — با تجمیع قیمت/موجودی
    // ==========================================

    /**
     * همگام‌سازی واریانت‌ها و تجمیع قیمت/موجودی روی محصول
     * طبق گزارش: stock محصول = جمع stock واریانت‌ها،
     * price محصول = قیمت ارزان‌ترین واریانتِ موجود
     */
    private function syncVariants(Product $product, ProductDTO $dto): void
    {
        $submitted = $dto->getVariants();

        // حذف واریانت‌هایی که دیگر در فرم نیستند
        $submittedIds = array_values(array_filter(array_column($submitted, 'id')));
        $variantsQuery = $product->variants();
        if ($submittedIds) {
            $variantsQuery->whereNotIn('id', $submittedIds);
        }
        $variantsQuery->get()->each->delete();

        foreach ($submitted as $variantData) {
            $finalPrice = ! empty($variantData['discount_price']) && $variantData['discount_price'] > 0
                ? $variantData['discount_price']
                : $variantData['price'];

            $variant = ProductVariant::updateOrCreate(
                ['id' => $variantData['id'] ?? null, 'product_id' => $product->id],
                [
                    'price' => $variantData['price'],
                    'discounted_price' => $variantData['discount_price'],
                    'final_price' => $finalPrice,
                    'stock' => $variantData['stock'],
                    'weight' => $variantData['weight'] ?: 100,
                ]
            );

            $variant->specifications()->sync($variantData['spec_ids']);
        }

        // تجمیع روی محصول اگر واریانت دارد
        $this->aggregateVariantPrices($product);
    }

    /**
     * تجمیع قیمت/موجودی واریانت‌ها روی محصول (بند ۴.۳ گزارش)
     * موجودی = جمع موجودی همه واریانت‌ها، قیمت = ارزان‌ترین واریانتِ موجود
     */
    private function aggregateVariantPrices(Product $product): void
    {
        if (! $product->variants()->exists()) {
            return;
        }

        $minVariant = $product->variants()
            ->where('stock', '>', 0)
            ->where('final_price', '>', 0)
            ->orderBy('final_price', 'asc')
            ->first();

        if ($minVariant) {
            $product->update([
                'price' => $minVariant->price,
                'discounted_price' => $minVariant->discounted_price,
                'final_price' => $minVariant->final_price,
            ]);
        }
    }

    // ==========================================
    // ویژگی‌های آزاد (Properties)
    // ==========================================

    private function syncProperties(Product $product, ProductDTO $dto): void
    {
        Property::where('product_id', $product->id)->delete();

        foreach ($dto->getProperties() as $index => $property) {
            Property::create([
                'product_id' => $product->id,
                'title' => $property['title'],
                'value' => $property['value'],
                'sort_order' => $index,
            ]);
        }
    }

    // ==========================================
    // محصولات مرتبط و مکمل (Associations)
    // ==========================================

    private function syncAssociations(Product $product, ProductDTO $dto): void
    {
        $product->associations()->delete();

        $associations = [];
        foreach ($dto->getRelatedIds() as $relatedId) {
            $associations[] = ['related_product_id' => $relatedId, 'type' => 'related'];
        }
        foreach ($dto->getComplementIds() as $complementId) {
            $associations[] = ['related_product_id' => $complementId, 'type' => 'complement'];
        }

        foreach ($associations as $assoc) {
            $product->associations()->create($assoc);
        }
    }

    // ==========================================
    // تصاویر (Images)
    // ==========================================

    /**
     * افزودن تصاویر جدید به گالری (بدون حذف تصاویر قبلی)
     *
     * @return array<int, ProductImage> ایندکس‌شده بر اساس ایندکس فایل ارسالی
     */
    private function appendImages(Product $product, array $uploadedFiles): array
    {
        $created = [];
        $startSort = (int) $product->images()->max('sort_order');

        foreach ($uploadedFiles as $index => $file) {
            $imagePath = ImageHandler::handle(self::UPLOAD_FOLDER, $file, self::IMAGE_SIZES);

            if (! $imagePath) {
                continue;
            }

            $created[$index] = $product->images()->create([
                'path' => $imagePath,
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
        if (! $target && $mainNewIndex !== null && isset($newImages[$mainNewIndex])) {
            $target = $newImages[$mainNewIndex];
        }

        // ۳) در غیر این صورت اولین تصویر گالری
        if (! $target) {
            $target = $product->images()->orderBy('sort_order')->first();
        }

        if (! $target) {
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
