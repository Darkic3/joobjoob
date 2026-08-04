<?php

namespace App\Services;

use App\DTOs\BrandDTO;
use App\Helpers\ImageHandler;
use App\Models\Brand;

class BrandService
{
    protected const UPLOAD_FOLDER = 'brands';

    protected const IMAGE_SIZES = [
        'medium' => [400, 400],
    ];

    public function store(BrandDTO $dto): Brand
    {
        $data = $this->prepareData($dto);

        $image = $dto->getImage();
        if ($image) {
            $data['image'] = ImageHandler::handle(self::UPLOAD_FOLDER, $image, self::IMAGE_SIZES);
        }

        return Brand::create($data);
    }

    public function update(Brand $brand, BrandDTO $dto): bool
    {
        $data = $this->prepareData($dto);

        $image = $dto->getImage();
        if ($image) {
            // حذف تصویر قبلی
            if ($brand->image) {
                ImageHandler::delete(self::UPLOAD_FOLDER, $brand->image, self::IMAGE_SIZES);
            }

            $data['image'] = ImageHandler::handle(self::UPLOAD_FOLDER, $image, self::IMAGE_SIZES);
        }

        return $brand->update($data);
    }

    public function destroy(Brand $brand): bool
    {
        if ($brand->image) {
            ImageHandler::delete(self::UPLOAD_FOLDER, $brand->image, self::IMAGE_SIZES);
        }

        // به لطف SoftDeletes حذف نرم انجام می‌شود
        return $brand->delete();
    }

    public function getPaginatedBrands(array $filters, int $perPage = 15)
    {
        return Brand::query()
            ->withCount('products')
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where('title->fa', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('title->en', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('url', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                $q->where('active', $filters['status']);
            })
            ->latest()
            ->paginate($perPage);
    }

    private function prepareData(BrandDTO $dto): array
    {
        return [
            'title'              => $dto->getTitle(),
            'url'                => $dto->getUrl(),
            'description'        => $dto->getDescription(),
            'active'             => $dto->getIsActive(),
            'show_in_first_page' => $dto->getShowInFirstPage(),
        ];
    }
}
