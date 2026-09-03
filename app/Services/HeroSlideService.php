<?php

namespace App\Services;

use App\DTOs\HeroSlideDTO;
use App\Helpers\ImageHandler;
use App\Models\HeroSlide;

class HeroSlideService
{
    protected const UPLOAD_FOLDER = 'hero-slides';

    public static function getSlides()
    {
        $slides = HeroSlide::active()->get();

        // Only eager-load 'linkable' for slides whose linkable_type is a real Eloquent model class
        // (skip special types like 'page' and 'url' which are plain strings, not classes)
        $slides->filter(fn ($s) => $s->linkable_type && class_exists($s->linkable_type))
            ->load('linkable');

        return $slides;
    }

    /**
     * لیست کامل اسلایدها به ترتیب نمایش (sort_order) — بدون صفحه‌بندی،
     * چون ترتیب با درگ‌اندروپ روی کل لیست انجام می‌شود (الگوی صفحهٔ زبان‌ها).
     */
    public function getOrdered()
    {
        return HeroSlide::orderBy('sort_order', 'asc')->orderBy('id')->get();
    }

    public function store(HeroSlideDTO $dto): HeroSlide
    {
        $data = $this->prepareData($dto);

        // ترتیب از فرم حذف شده — اسلاید جدید همیشه به انتهای لیست اضافه می‌شود (سورت از ایندکس با درگ‌اندروپ)
        $data['sort_order'] = (int) HeroSlide::max('sort_order') + 1;

        $data['image'] = $this->handleImageUpload($dto->image);

        return HeroSlide::create($data);
    }

    public function update(HeroSlide $slide, HeroSlideDTO $dto): bool
    {
        $data = $this->prepareData($dto);
        if ($dto->image) {
            $this->deleteImage($slide->image);
            $data['image'] = $this->handleImageUpload($dto->image);
        } elseif ($dto->removeImage && $slide->image) {
            // حذف واقعی تصویر فعلی (دکمهٔ حذف در کامپوننت آپلود)
            $this->deleteImage($slide->image);
            $data['image'] = null;
        }

        return $slide->update($data);
    }

    public function destroy(HeroSlide $slide): bool
    {
        $this->deleteImage($slide->image);

        return $slide->delete();
    }

    private function prepareData(HeroSlideDTO $dto): array
    {
        return [
            'title' => $dto->title,
            'desc' => $dto->desc,
            'badge' => $dto->badge,
            'btn_text' => $dto->btn_text,
            'linkable_type' => $dto->getLinkableType(),
            'linkable_id' => $dto->getLinkableId(),
            'price' => $dto->price ?? 0,
            'old_price' => $dto->old_price,
            'currency' => $dto->currency,
            'rating' => $dto->rating,
            'viewers' => $dto->viewers ?? 0,
            'trusts' => $dto->trusts,
            'is_active' => $dto->is_active,
        ];
    }

    private function handleImageUpload(?object $image): ?string
    {
        if (! $image) {
            return null;
        }

        return ImageHandler::handle(self::UPLOAD_FOLDER, $image);
    }

    private function deleteImage(?string $imageName): void
    {
        if (! $imageName) {
            return;
        }
        ImageHandler::delete(self::UPLOAD_FOLDER, $imageName);
    }
}
