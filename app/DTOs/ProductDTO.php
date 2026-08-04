<?php

namespace App\DTOs;

use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Http\UploadedFile;

readonly class ProductDTO
{
    public function __construct(
        private int             $productCategoryId,
        private ?int            $brandId,
        private array           $name,
        private string          $slug,
        private ?array          $shortDescription,
        private ?array          $description,
        private ?string         $sku,
        private int             $price,
        private ?int            $discountPrice,
        private int             $stock,
        private ?int            $weight,
        private bool            $isActive,
        private bool            $isFeatured,
        private bool            $isBestSeller,
        private ?string         $dealEndsAt,
        private ?string         $startTimer,
        private ?string         $endTimer,
        private bool            $timerActive,
        private array           $specs,        // [parent_id => [child specification_id, ...]] — مشخصه‌های انتخابی
        private array           $textSpecs,    // [specification_id => value] — مقادیر نوشتاری
        private array           $variants,     // [['id'=>?, 'spec_ids'=>[], 'price'=>, 'discount_price'=>, 'stock'=>, 'weight'=>], ...]
        private array           $properties,   // [['id'=>?, 'title'=>, 'value'=>], ...]
        private array           $relatedIds,   // شناسه محصولات مرتبط
        private array           $complementIds,// شناسه محصولات مکمل
        private array           $images,       // آرایه‌ای از UploadedFile (تصاویر جدید)
        private array           $deleteImages, // شناسه‌های تصاویر موجود که باید حذف شوند
        private ?int            $mainImageId,  // شناسه تصویر اصلی (موجود)
        private ?int            $mainNewIndex, // ایندکس تصویر اصلی در میان تصاویر جدید
    ) {}

    public static function fromRequest(ProductRequest $request): self
    {
        $variants = [];
        foreach ((array) $request->input('variants', []) as $v) {
            // ردیف‌های خالی (بدون قیمت و موجودی) نادیده گرفته می‌شوند
            $price = (int) ($v['price'] ?? 0);
            $stock = (int) ($v['stock'] ?? 0);
            if ($price <= 0 && $stock <= 0 && empty($v['id'])) {
                continue;
            }

            $variants[] = [
                'id'             => !empty($v['id']) ? (int) $v['id'] : null,
                'spec_ids'       => array_values(array_filter(array_map('intval', (array) ($v['spec_ids'] ?? [])))),
                'price'          => $price,
                'discount_price' => (int) ($v['discount_price'] ?? 0),
                'stock'          => $stock,
                'weight'         => (int) ($v['weight'] ?? 100),
            ];
        }

        $properties = [];
        foreach ((array) $request->input('properties', []) as $p) {
            $title = trim((string) ($p['title'] ?? ''));
            $value = trim((string) ($p['value'] ?? ''));
            if ($value === '' || $title === '') {
                continue;
            }

            $properties[] = [
                'id'    => !empty($p['id']) ? (int) $p['id'] : null,
                'title' => ['fa' => $title, 'en' => $title],
                'value' => $value,
            ];
        }

        return new self(
            productCategoryId: $request->validated('product_category_id'),
            brandId: $request->input('brand_id') ? (int) $request->input('brand_id') : null,
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            shortDescription: $request->validated('short_description'),
            description: $request->validated('description'),
            sku: $request->validated('sku'),
            price: $request->integer('price'),
            discountPrice: $request->integer('discount_price') ?: null,
            stock: $request->integer('stock'),
            weight: $request->integer('weight'),
            isActive: $request->boolean('is_active', true),
            isFeatured: $request->boolean('is_featured'),
            isBestSeller: $request->boolean('is_best_seller'),
            dealEndsAt: $request->validated('deal_ends_at'),
            startTimer: $request->input('start_timer'),
            endTimer: $request->input('end_timer'),
            timerActive: $request->boolean('timer_active'),
            specs: $this->normalizeSpecs($request->input('specs', [])),
            textSpecs: $this->normalizeTextSpecs($request->input('text_specs', [])),
            variants: $variants,
            properties: $properties,
            relatedIds: array_values(array_filter(array_map('intval', (array) $request->input('related_ids', [])))),
            complementIds: array_values(array_filter(array_map('intval', (array) $request->input('complement_ids', [])))),
            images: $request->file('images') ?? [],
            deleteImages: array_values(array_filter(array_map('intval', (array) $request->input('delete_images', [])))),
            mainImageId: $request->input('main_image_id') ? (int) $request->input('main_image_id') : null,
            mainNewIndex: $request->input('main_new_index') !== '' && $request->input('main_new_index') !== null ? (int) $request->input('main_new_index') : null,
        );
    }

    /**
     * مشخصه‌های انتخابی: [parent_id => [child_id, ...]]
     */
    private static function normalizeSpecs($input): array
    {
        $result = [];
        foreach ((array) $input as $parentId => $children) {
            $ids = array_values(array_filter(array_map('intval', (array) $children)));
            if (!empty($ids)) {
                $result[(int) $parentId] = $ids;
            }
        }
        return $result;
    }

    /**
     * مقادیر نوشتاری: [specification_id => value]
     */
    private static function normalizeTextSpecs($input): array
    {
        $result = [];
        foreach ((array) $input as $specId => $value) {
            $value = trim((string) $value);
            if ($value !== '') {
                $result[(int) $specId] = $value;
            }
        }
        return $result;
    }

    // Getters
    public function getProductCategoryId(): int { return $this->productCategoryId; }
    public function getBrandId(): ?int { return $this->brandId; }
    public function getName(): array { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getShortDescription(): ?array { return $this->shortDescription; }
    public function getDescription(): ?array { return $this->description; }
    public function getSku(): ?string { return $this->sku; }
    public function getPrice(): int { return $this->price; }
    public function getDiscountPrice(): ?int { return $this->discountPrice; }
    public function getStock(): int { return $this->stock; }
    public function getWeight(): ?int { return $this->weight; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getIsFeatured(): bool { return $this->isFeatured; }
    public function getIsBestSeller(): bool { return $this->isBestSeller; }
    public function getDealEndsAt(): ?string { return $this->dealEndsAt; }
    public function getStartTimer(): ?string { return $this->startTimer; }
    public function getEndTimer(): ?string { return $this->endTimer; }
    public function getTimerActive(): bool { return $this->timerActive; }
    public function getSpecs(): array { return $this->specs; }
    public function getTextSpecs(): array { return $this->textSpecs; }
    public function getVariants(): array { return $this->variants; }
    public function getProperties(): array { return $this->properties; }
    public function getRelatedIds(): array { return $this->relatedIds; }
    public function getComplementIds(): array { return $this->complementIds; }
    public function getImages(): array { return $this->images; }
    public function getDeleteImages(): array { return $this->deleteImages; }
    public function getMainImageId(): ?int { return $this->mainImageId; }
    public function getMainNewIndex(): ?int { return $this->mainNewIndex; }
}
