<?php

namespace App\DTOs;

use App\Http\Requests\Admin\ProductCategoryRequest;
use Illuminate\Http\UploadedFile;

readonly class ProductCategoryDTO
{
    public function __construct(
        private ?int $parentId,
        private array $name,
        private string $slug,
        private ?string $icon,
        private ?UploadedFile $image,
        private ?array $description,
        private ?array $metaTitle,
        private ?array $metaDescription,
        private int $sortOrder,
        private bool $isActive,
    ) {}

    public static function fromRequest(ProductCategoryRequest $request): self
    {
        return new self(
            parentId: $request->validated('parent_id'),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            icon: $request->validated('icon'),
            // ⚠️ hasFile به‌جای file: مرورگر حتی بدون انتخاب تصویر، فیلد خالی را با error=4 ارسال می‌کند
            // و file() یک UploadedFile نامعتبر برمی‌گرداند که store() روی آن با «Path cannot be empty» می‌ترکد.
            image: $request->hasFile('image') ? $request->file('image') : null,
            description: $request->validated('description'),
            metaTitle: $request->validated('meta_title'),
            metaDescription: $request->validated('meta_description'),
            sortOrder: $request->integer('sort_order', 0),
            isActive: $request->boolean('is_active', true),
        );
    }

    // Getters
    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getName(): array
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function getDescription(): ?array
    {
        return $this->description;
    }

    public function getMetaTitle(): ?array
    {
        return $this->metaTitle;
    }

    public function getMetaDescription(): ?array
    {
        return $this->metaDescription;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
