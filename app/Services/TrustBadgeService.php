<?php

namespace App\Services;

use App\DTOs\TrustBadgeDTO;
use App\Models\TrustBadge;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class TrustBadgeService
{
    /**
     * کلید کش برای نگهداری لیست نشان‌های فعال
     */
    private const CACHE_KEY = 'trust_badges_active';

    /**
     * حداکثر تعداد نشان‌هایی که در سایت (صفحهٔ اصلی) نمایش داده می‌شود
     */
    public const SITE_MAX_BADGES = 6;

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
     * نشان‌های فعال برای نمایش در سایت (صفحهٔ اصلی) — با کش و محدودیت.
     *
     * همهٔ نشان‌ها با یک کوئری از دیتابیس خوانده می‌شود (اگر کش موجود نباشد)
     * و برای ۱ ساعت کش می‌ماند؛ هر تغییری در پنل ادمین کش را پاک می‌کند.
     * محدودیت نمایش (SITE_MAX_BADGES) این‌جا اعمال می‌شود تا ویو فقط مصرف‌کننده باشد.
     */
    public function getActiveForSite(int $limit = self::SITE_MAX_BADGES): Collection
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () use ($limit) {
            return TrustBadge::activeOrdered()->limit($limit)->get();
        });
    }

    /**
     * لیست کامل نشان‌ها به ترتیب نمایش (sort_order) — بدون صفحه‌بندی،
     * چون ترتیب با درگ‌اندروپ روی کل لیست انجام می‌شود (الگوی صفحهٔ زبان‌ها).
     */
    public function getOrderedBadges(array $filters)
    {
        return TrustBadge::query()
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('title->fa', 'like', $term)
                        ->orWhere('title->en', 'like', $term)
                        ->orWhere('description->fa', 'like', $term)
                        ->orWhere('description->en', 'like', $term);
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
