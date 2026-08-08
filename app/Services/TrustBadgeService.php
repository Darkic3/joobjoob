<?php

namespace App\Services;

use App\DTOs\TrustBadgeDTO;
use App\Models\TrustBadge;
use Illuminate\Support\Facades\Cache;

class TrustBadgeService
{
    /**
     * کلید کش برای نگهداری لیست نشان‌های فعال
     */
    private const CACHE_KEY = 'trust_badges_active';

    public function store(TrustBadgeDTO $dto): TrustBadge
    {
        $data = $this->prepareData($dto);

        // ترتیب از فرم حذف شده — نشان جدید همیشه به انتهای لیست اضافه می‌شود (سورت از ایندکس با درگ‌اندروپ)
        $data['sort_order'] = (int) TrustBadge::max('sort_order') + 1;

        $badge = TrustBadge::create($data);

        $this->clearCache();

        return $badge;
    }

    public function update(TrustBadge $badge, TrustBadgeDTO $dto): bool
    {
        $data = $this->prepareData($dto);
        $result = $badge->update($data);

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    public function destroy(TrustBadge $badge): bool
    {
        // به لطف SoftDeletes در مدل، حذف نرم انجام می‌شود
        $result = $badge->delete();

        if ($result) {
            $this->clearCache();
        }

        return $result;
    }

    /**
     * لیست کامل نشان‌ها به ترتیب نمایش (sort_order) — بدون صفحه‌بندی،
     * چون ترتیب با درگ‌اندروپ روی کل لیست انجام می‌شود (الگوی صفحهٔ زبان‌ها).
     */
    public function getOrderedBadges(array $filters)
    {
        return TrustBadge::query()
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
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
    private function prepareData(TrustBadgeDTO $dto): array
    {
        return [
            'icon' => $dto->getIcon(),
            'title' => $dto->getTitle(),
            'description' => $dto->getDescription(),
            'is_active' => $dto->getIsActive(),
        ];
    }

    /**
     * پاکسازی کش پس از هرگونه تغییر (ایجاد، ویرایش، حذف، ترتیب)
     * تا تغییرات بلافاصله در فرانت‌اند اعمال شود.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
