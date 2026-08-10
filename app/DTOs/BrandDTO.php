<?php

namespace App\DTOs;

use App\Http\Requests\Admin\BrandRequest;
use Illuminate\Http\UploadedFile;

readonly class BrandDTO
{
    public function __construct(
        private array $title,
        private string $url,
        private ?array $description,
        private ?UploadedFile $image,
        private bool $removeImage,
        private bool $isActive,
        private bool $showInFirstPage,
    ) {}

    public static function fromRequest(BrandRequest $request): self
    {
        return new self(
            title: $request->validated('title'),
            url: $request->validated('url'),
            description: $request->validated('description'),
            image: $request->file('image'),
            removeImage: $request->boolean('remove_image'),
            isActive: $request->boolean('active', true),
            showInFirstPage: $request->boolean('show_in_first_page'),
        );
    }

    // Getters
    public function getTitle(): array
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getDescription(): ?array
    {
        return $this->description;
    }

    public function getImage(): ?UploadedFile
    {
        return $this->image;
    }

    public function getRemoveImage(): bool
    {
        return $this->removeImage;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getShowInFirstPage(): bool
    {
        return $this->showInFirstPage;
    }
}
