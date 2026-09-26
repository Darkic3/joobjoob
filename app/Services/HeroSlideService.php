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
     * هیرو فعلی سایت — همان رکوردی که صفحهٔ اصلی نمایش می‌دهد:
     * اولین اسلاید فعال (ترتیب sort_order)؛ اگر هیچ فعالی نبود اولین اسلاید موجود.
     */
    public function primaryForAdmin(): ?HeroSlide
    {
        return HeroSlide::query()
            ->when(
                HeroSlide::where('is_active', true)->exists(),
                fn ($q) => $q->where('is_active', true)
            )
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->first();
    }

    /**
     * ذخیرهٔ هیرو (اگر اسلایدی نباشد ساخته می‌شود) — یک‌جا آپلود/حذف تصویر.
     */
    public function save(?HeroSlide $slide, HeroSlideDTO $dto): HeroSlide
    {
        $data = $this->prepareData($dto);

        if ($slide) {
            $newImage = $dto->image ? $this->handleImageUpload($dto->image) : null;
            if ($newImage) {
                $this->deleteImage($slide->image);
                $data['image'] = $newImage;
            } elseif ($dto->removeImage && $slide->image) {
                // حذف واقعی تصویر فعلی (دکمهٔ حذف در کامپوننت آپلود)
                $this->deleteImage($slide->image);
                $data['image'] = null;
            }
            $slide->update($data);

            return $slide;
        }

        $data['sort_order'] = (int) HeroSlide::max('sort_order') + 1;
        if ($dto->image) {
            $data['image'] = $this->handleImageUpload($dto->image);
        }

        return HeroSlide::create($data);
    }

    /**
     * حذف اسلایدهای اضافی (نسخهٔ مینیمال تنها یک هیرو نمایش می‌دهد) —
     * بهجز اسلاید اصلیِ داده‌شده.
     *
     * @return int تعداد حذف‌شده‌ها
     */
    public function pruneOthers(?HeroSlide $keep): int
    {
        if (! $keep) {
            return 0;
        }

        $others = HeroSlide::whereKeyNot($keep->getKey())->get();
        foreach ($others as $slide) {
            $this->deleteImage($slide->image);
            $slide->delete();
        }

        return $others->count();
    }

    /**
     * تعداد اسلایدهای اضافی (غیر از هیرو اصلی) — برای اطلاع‌رسانی در فرم ادمین.
     */
    public function extraCount(?HeroSlide $primary): int
    {
        if (! $primary) {
            return HeroSlide::count();
        }

        return HeroSlide::whereKeyNot($primary->getKey())->count();
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
