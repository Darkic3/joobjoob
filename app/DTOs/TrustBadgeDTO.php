<?php

namespace App\DTOs;

use App\Http\Requests\Admin\TrustBadgeRequest;

readonly class TrustBadgeDTO
{
    public function __construct(
        private string  $icon,
        private string  $title,
        private ?string $description,
        private int     $sortOrder,
        private bool    $isActive,
    ) {}

    public static function fromRequest(TrustBadgeRequest $request): self
    {
        return new self(
            icon: $request->validated('icon'),
            title: $request->validated('title'),
            description: $request->validated('description'),
            sortOrder: (int) $request->validated('sort_order'),
            isActive: $request->boolean('is_active'),
        );
    }

    // Getters
    public function getIcon(): string { return $this->icon; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function getIsActive(): bool { return $this->isActive; }
}
