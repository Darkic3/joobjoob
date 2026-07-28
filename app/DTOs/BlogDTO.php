<?php

namespace App\DTOs;

use Illuminate\Http\UploadedFile;

readonly class BlogDTO
{
    public function __construct(
        public string        $title,
        public string        $slug,
        public ?string       $excerpt,
        public string        $content,
        public ?int          $blog_category_id,
        public ?UploadedFile $image,
        public bool          $is_active,
        public bool          $show_in_first_page,
        public int           $user_id,
    )
    {
    }

    /**
     *  DTO  Request
     */
    public static function fromRequest(array $validated, int $userId): self
    {
        return new self(
            title: $validated['title'],
            slug: $validated['slug'],
            excerpt: $validated['excerpt'] ?? null,
            content: $validated['content'],
            blog_category_id: $validated['blog_category_id'] ?? null,
            image: $validated['image'] ?? null,
            is_active: $validated['is_active'] ?? true,
            show_in_first_page: $validated['show_in_first_page'] ?? false,
            user_id: $userId,
        );
    }

    /**
     * تبدیل DTO به آرایه برای ذخیره در دیتابیس (بدون فایل)
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'blog_category_id' => $this->blog_category_id,
            'is_active' => $this->is_active,
            'show_in_first_page' => $this->show_in_first_page,
            'user_id' => $this->user_id,
        ];
    }
}
