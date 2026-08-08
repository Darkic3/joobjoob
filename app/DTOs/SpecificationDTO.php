<?php

namespace App\DTOs;

use App\Http\Requests\Admin\SpecificationRequest;

readonly class SpecificationDTO
{
    public function __construct(
        private array    $title,
        private string   $type,
        private ?int     $parentId,
        private bool     $isActive,
        private bool     $isFilter,
        private bool     $isColor,
        private ?string  $colorCode,
    ) {}

    public static function fromRequest(SpecificationRequest $request): self
    {
        $type = $request->validated('type', 'text');

        return new self(
            title: $request->validated('title'),
            type: $type,
            parentId: $request->input('parent_id') ? (int) $request->input('parent_id') : null,
            isActive: $request->boolean('active', true),
            isFilter: $type === 'select' && $request->boolean('is_filter'),
            isColor: $type === 'select' && $request->boolean('is_color'),
            colorCode: $request->input('color_code') ?: null,
        );
    }

    // Getters
    public function getTitle(): array { return $this->title; }
    public function getType(): string { return $this->type; }
    public function getParentId(): ?int { return $this->parentId; }
    public function getIsActive(): bool { return $this->isActive; }
    public function getIsFilter(): bool { return $this->isFilter; }
    public function getIsColor(): bool { return $this->isColor; }
    public function getColorCode(): ?string { return $this->colorCode; }
}
