<?php

namespace App\Services;

use App\DTOs\HeroSlideDTO;
use App\Helpers\ImageHandler;
use App\Models\HeroSlide;
use Illuminate\Support\Facades\File;

class HeroSlideService
{
    protected const UPLOAD_FOLDER = 'hero-slides';

    public static function getSlides()
    {
        return HeroSlide::active()->get();
    }

    public function getPaginated(int $perPage = 15)
    {
        return HeroSlide::orderBy('sort_order', 'asc')
            ->latest('id')
            ->paginate($perPage);
    }

    public function store(HeroSlideDTO $dto): HeroSlide
    {
        $data = $this->prepareData($dto);
        $data['image'] = $this->handleImageUpload($dto->image);
        return HeroSlide::create($data);
    }

    public function update(HeroSlide $slide, HeroSlideDTO $dto): bool
    {
        $data = $this->prepareData($dto);
        if ($dto->image) {
            $this->deleteImage($slide->image);
            $data['image'] = $this->handleImageUpload($dto->image);
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
            'title'         => $dto->title,
            'desc'          => $dto->desc,
            'badge'         => $dto->badge,
            'btn_text'      => $dto->btn_text,
            'linkable_type' => $dto->getLinkableType(),
            'linkable_id'   => $dto->getLinkableId(),
            'price'         => $dto->price ?? 0,
            'old_price'     => $dto->old_price,
            'currency'      => $dto->currency,
            'rating'        => $dto->rating,
            'viewers'       => $dto->viewers ?? 0,
            'is_active'     => $dto->is_active,
            'sort_order'    => $dto->sort_order,
        ];
    }

    private function handleImageUpload(?object $image): ?string
    {
        if (!$image) return null;
        return ImageHandler::handle(self::UPLOAD_FOLDER, $image);
    }

    private function deleteImage(?string $imageName): void
    {
        if (!$imageName) return;
        $baseUpload = config('paths.upload', 'uploads');
        $fullPath = public_path("{$baseUpload}/" . self::UPLOAD_FOLDER . "/{$imageName}");
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }
    }
}
