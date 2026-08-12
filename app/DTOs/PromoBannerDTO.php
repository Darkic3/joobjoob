<?php

namespace App\DTOs;

use App\Http\Requests\Admin\PromoBannerRequest;

readonly class PromoBannerDTO
{
    public function __construct(
        private string $icon,
        private array $title,
        private ?array $subtitle,
        private ?string $url,
        private string $style,
        private string $tone,
        private bool $isActive,
    ) {}

    public static function fromRequest(PromoBannerRequest $request): self
    {
        $subtitleFa = trim((string) $request->validated('subtitle.fa', ''));
        $subtitleEn = trim((string) $request->validated('subtitle.en', ''));

        return new self(
            icon: $request->validated('icon', 'megaphone'),
            title: [
                'fa' => (string) $request->validated('title.fa', ''),
                'en' => (string) $request->validated('title.en', ''),
            ],
            subtitle: ($subtitleFa === '' && $subtitleEn === '')
                ? null
                : ['fa' => $subtitleFa, 'en' => $subtitleEn],
            url: $request->validated('url') ?: null,
            style: $request->validated('style', 'gradient'),
            tone: $request->validated('tone', 'accent'),
            isActive: $request->boolean('is_active'),
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

    public function getSubtitle(): ?array
    {
        return $this->subtitle;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getStyle(): string
    {
        return $this->style;
    }

    public function getTone(): string
    {
        return $this->tone;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }
}
