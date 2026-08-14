<?php

namespace App\DTOs;

use App\Http\Requests\Admin\TrustBadgeRequest;

readonly class TrustBadgeDTO
{
    public function __construct(
        private string $icon,
        private array $title,
        private ?array $description,
        private bool $isActive,
        private string $placement = 'site',
    ) {}

    public static function fromRequest(TrustBadgeRequest $request): self
    {
        $fa = trim((string) $request->validated('description.fa', ''));
        $en = trim((string) $request->validated('description.en', ''));

        return new self(
            icon: $request->validated('icon'),
            title: [
                'fa' => (string) $request->validated('title.fa', ''),
                'en' => (string) $request->validated('title.en', ''),
            ],
            description: ($fa === '' && $en === '') ? null : ['fa' => $fa, 'en' => $en],
            isActive: $request->boolean('is_active'),
            placement: $request->validated('placement', 'site'),
        );
    }

    // Getters
    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getTitle(): array
    {
        return $this->title;
    }

    public function getDescription(): ?array
    {
        return $this->description;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function getPlacement(): string
    {
        return $this->placement;
    }
}
