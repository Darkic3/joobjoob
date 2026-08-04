<?php

namespace App\DTOs;

use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Http\UploadedFile;

readonly class ProductDTO
{
    public function __construct(
        private int             $productCategoryId,
        private array           $name,
        private string          $slug,
        private ?array          $shortDescription,
        private ?array          $description,
        private ?string         $sku,
        private ?string         $brand,
        private int             $price,
        private ?int            $discountPrice,
        private int             $stock,
        private ?int            $weight,
        private bool            $isActive,
        private bool            $isFeatured,
        private bool            $isBestSeller,
        private ?array          $colors,
        private ?array          $sizes,
        private ?string         $dealEndsAt,
        private array           $images,      // آرایه‌ای از UploadedFile (تصاویر جدید)
        private array           $deleteImages, // شناسه‌های تصاویر موجود که باید حذف شوند
        private ?int            $mainImageId, // شناسه تصویر اصلی (موجود)
        private ?int            $mainNewIndex, // ایندکس تصویر اصلی در میان تصاویر جدید
    ) {}

    public static function fromRequest(ProductRequest $request): self
    {
        return new self(
            productCategoryId: $request->validated('product_category_id'),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            shortDescription: $request->validated('short_description'),
            description: $request->validated('description'),
            sku: $request->validated('sku'),
            brand: $request->validated('brand'),
            price: $request->integer('price'),
            discountPrice: $request->integer('discount_price'),
            stock: $request->integer('stock'),
            weight: $request->integer('weight'),
            isActive: $request->boolean('is_active', true),
            isFeatured: $request->boolean('is_featured'),
            isBestSeller: $request->boolean('is_best_seller'),
            colors: $request->validated('colors'),
            sizes: $request->validated('sizes'),
            dealEndsAt: $request->validated('deal_ends_at'),
            images: $request->file('images') ?? [],
            deleteImages: array_values(array_filter(array_map('intval', (array) $request->input('delete_images', [])))),
            mainImageId: $request->input('main_image_id') ? (int) $request->input('main_image_id') : null,
            mainNewIndex: $request->input('main_new_index') !== '' && $request->input('main_new_index') !== null ? (int) $request->input('main_new_index') : null,
        );
    }

    // Getters
    public function getProductCategoryId(): int { return $this->productCategoryId; }
    public function getName(): array { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getShortDescription(): ?array { return $this->shortDescription; }
    public function getDescription(): ?array { return $this->description; }
    public function getSku(): ?string { return $this->sku; }
    public function getBrand(): ?string { return $this->brand; }
    public function getPrice(): int { return $this->price; }
    public function getDiscountPrice(): ?int { return $this->discountPrice; }
    public function getStock(): int { return $this->stock; }
    public function getWeight(): ?int { return $this->weight; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getIsFeatured(): bool { return $this->isFeatured; }
    public function getIsBestSeller(): bool { return $this->isBestSeller; }
    public function getColors(): ?array { return $this->colors; }
    public function getSizes(): ?array { return $this->sizes; }
    public function getDealEndsAt(): ?string { return $this->dealEndsAt; }
    public function getImages(): array { return $this->images; }
    public function getDeleteImages(): array { return $this->deleteImages; }
    public function getMainImageId(): ?int { return $this->mainImageId; }
    public function getMainNewIndex(): ?int { return $this->mainNewIndex; }
}
