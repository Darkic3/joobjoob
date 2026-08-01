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
        private string          $status,
        private bool            $isFeatured,
        private bool            $isBestSeller,
        private ?array          $colors,
        private ?array          $sizes,
        private ?string         $dealEndsAt,
        private array           $images, // آرایه‌ای از UploadedFile
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
            status: $request->validated('status'),
            isFeatured: $request->boolean('is_featured'),
            isBestSeller: $request->boolean('is_best_seller'),
            colors: $request->validated('colors'),
            sizes: $request->validated('sizes'),
            dealEndsAt: $request->validated('deal_ends_at'),
            images: $request->file('images') ?? [],
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
    public function getStatus(): string { return $this->status; }
    public function getIsFeatured(): bool { return $this->isFeatured; }
    public function getIsBestSeller(): bool { return $this->isBestSeller; }
    public function getColors(): ?array { return $this->colors; }
    public function getSizes(): ?array { return $this->sizes; }
    public function getDealEndsAt(): ?string { return $this->dealEndsAt; }
    public function getImages(): array { return $this->images; }
}
