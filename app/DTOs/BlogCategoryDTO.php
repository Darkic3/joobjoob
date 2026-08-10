<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly class BlogCategoryDTO
{
    public function __construct(
        public ?int $parent_id,
        public string $title,
        public string $slug,
        public ?string $description,
        public ?UploadedFile $image,
        public bool $is_active,
        public bool $show_in_first_page,
        public int $sort_order,
    ) {}

    /**
     *DTO Request
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            parent_id: $validated['parent_id'] ?? null,
            title: $validated['title'],
            slug: $validated['slug'],
            description: $validated['description'] ?? null,
            image: $validated['image'] ?? null,
            is_active: $validated['is_active'] ?? true,
            show_in_first_page: $validated['show_in_first_page'] ?? false,
            sort_order: $validated['sort_order'] ?? 0,
        );
    }

    /**
     * تبدیل DTO به آرایه برای ذخیره در دیتابیس (بدون فایل)
     */
    public function toArray(): array
    {
        return [
            'parent_id' => $this->parent_id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'show_in_first_page' => $this->show_in_first_page,
            'sort_order' => $this->sort_order,
        ];
    }
}
