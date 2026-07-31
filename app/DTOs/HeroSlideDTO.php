<?php

namespace App\DTOs;

use App\Http\Requests\Admin\HeroSlideRequest;
use Illuminate\Http\UploadedFile;

class HeroSlideDTO
{
    public function __construct(
        public array $title,
        public array $desc,
        public ?array $badge,
        public ?array $btn_text,
        public ?array $price,
        public ?array $old_price,
        public ?UploadedFile $image,
        public string $btn_link,
        public bool $is_active,
        public int $sort_order,
    ) {}

    public static function fromRequest(HeroSlideRequest $request): self
    {
        return new self(
            title: [
                'fa' => $request->input('title.fa'),
                'en' => $request->input('title.en'),
            ],
            desc: [
                'fa' => $request->input('desc.fa'),
                'en' => $request->input('desc.en'),
            ],
            badge: [
                'fa' => $request->input('badge.fa'),
                'en' => $request->input('badge.en'),
            ],
            btn_text: [
                'fa' => $request->input('btn_text.fa'),
                'en' => $request->input('btn_text.en'),
            ],
            price: [
                'fa' => $request->input('price.fa'),
                'en' => $request->input('price.en'),
            ],
            old_price: [
                'fa' => $request->input('old_price.fa'),
                'en' => $request->input('old_price.en'),
            ],
            image: $request->file('image'),
            btn_link: $request->input('btn_link'),
            is_active: $request->boolean('is_active'),
            sort_order: $request->integer('sort_order', 0),
        );
    }
}
