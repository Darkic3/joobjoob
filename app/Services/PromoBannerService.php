<?php

namespace App\Services;

use App\DTOs\PromoBannerDTO;
use App\Models\PromoBanner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PromoBannerService
{
    /**
     * کلید کش برای نگهداری لیست بنرهای فعال (نمایش در سایت)
     */
    private const CACHE_KEY = 'promo_banners_active';

    /**
     * حداکثر تعداد بنرهایی که در صفحهٔ اصلی نمایش داده می‌شود
     * (به اندازه‌ای باز شده تا هر ۵ سبک بنر + یک نمونهٔ اضافه در اسلایدر قابل نمایش باشد)
     */
    public const SITE_MAX_BANNERS = 6;

    public function store(PromoBannerDTO $dto): PromoBanner
    {
        $data = $this->prepareData($dto);

        // ترتیب از فرم حذف شده — بنر جدید همیشه به انتهای لیست اضافه می‌شود (سورت از ایندکس با درگ‌اندروپ)
        $data['sort_order'] = (int) PromoBanner::max('sort_order') + 1;

        $banner = PromoBanner::create($data);

        $this->clearCache();

        return $banner;
    }

    public function update(PromoBanner $banner, PromoBannerDTO $dto): bool
    {
        $result = $banner->update($this->prepareData($dto));

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    public function destroy(PromoBanner $banner): bool
    {
        $result = $banner->delete();

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    /**
     * بنرهای فعال برای نمایش در صفحهٔ اصلی — با کش و محدودیت.
     */
    public function getActiveForSite(int $limit = self::SITE_MAX_BANNERS): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () use ($limit) {
            return PromoBanner::activeOrdered()->limit($limit)->get();
        });
    }

    /**
     * لیست کامل بنرها به ترتیب نمایش (sort_order) — بدون صفحه‌بندی،
     * چون ترتیب با درگ‌اندروپ روی کل لیست انجام می‌شود (الگوی صفحهٔ زبان‌ها).
     */
    public function getOrderedBanners(array $filters)
    {
        return PromoBanner::query()
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title->fa', 'like', $term)
                        ->orWhere('title->en', 'like', $term)
                        ->orWhere('subtitle->fa', 'like', $term)
                        ->orWhere('subtitle->en', 'like', $term);
                });
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                $q->where('is_active', $filters['status']);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * تبدیل DTO به آرایه مناسب برای ذخیره در دیتابیس
     */
    private function prepareData(PromoBannerDTO $dto): array
    {
        return [
            'icon' => $dto->getIcon(),
            'title' => $dto->getTitle(),
            'subtitle' => $dto->getSubtitle(),
            'url' => $dto->getUrl(),
            'style' => $dto->getStyle(),
            'tone' => $dto->getTone(),
            'is_active' => $dto->getIsActive(),
        ];
    }

    /**
     * پاکسازی کش پس از هرگونه تغییر (ایجاد، ویرایش، حذف، ترتیب)
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
