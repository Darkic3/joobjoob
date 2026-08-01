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
     * دریافت لیست صفحه‌بندی شده با قابلیت فیلتر
     */
    public function getPaginatedBadges(array $filters, int $perPage = 15)
    {
        return TrustBadge::query()
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $q->where('title', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            })
            ->when(isset($filters['status']) && $filters['status'] !== '', function ($q) use ($filters) {
                $q->where('is_active', $filters['status']);
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * تبدیل DTO به آرایه مناسب برای ذخیره در دیتابیس
     */
    private function prepareData(TrustBadgeDTO $dto): array
    {
        return [
            'icon'        => $dto->getIcon(),
            'title'       => $dto->getTitle(),
            'description' => $dto->getDescription(),
            'sort_order'  => $dto->getSortOrder(),
            'is_active'   => $dto->getIsActive(),
        ];
    }

    /**
     * پاکسازی کش پس از هرگونه تغییر (ایجاد، ویرایش، حذف)
     * تا تغییرات بلافاصله در فرانت‌اند اعمال شود.
     */
    private function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
