<?php

namespace App\DTOs;

use App\Http\Requests\Admin\BlogRequest;
use Illuminate\Http\UploadedFile;

readonly class BlogDTO
{
    public function __construct(
        private string        $title,
        private string        $slug,
        private ?string       $excerpt,
        private string        $content,
        private ?int          $blogCategoryId,
        private bool          $isActive,
        private bool          $showInFirstPage,
        private ?UploadedFile $image,
        private bool          $removeImage = false,
        private ?int          $userId = null,
    ) {}

    public static function fromRequest(BlogRequest $request, ?int $userId = null): self
    {
        return new self(
            title: $request->validated('title'),
            slug: $request->validated('slug'),
            excerpt: $request->validated('excerpt'),
            content: $request->validated('content'),
            blogCategoryId: $request->validated('blog_category_id'),
            isActive: $request->boolean('is_active'),
            showInFirstPage: $request->boolean('show_in_first_page'),
            image: $request->file('image'),
            removeImage: $request->boolean('remove_image'),
            userId: $userId
        );
    }

    // Getters
    public function getTitle(): string { return $this->title; }
    public function getSlug(): string { return $this->slug; }
    public function getExcerpt(): ?string { return $this->excerpt; }
    public function getContent(): string { return $this->content; }
    public function getBlogCategoryId(): ?int { return $this->blogCategoryId; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getShowInFirstPage(): bool { return $this->showInFirstPage; }
    public function getImage(): ?UploadedFile { return $this->image; }
    public function getRemoveImage(): bool { return $this->removeImage; }
    public function getUserId(): ?int { return $this->userId; }
}
