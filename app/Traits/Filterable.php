<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    public function scopeFilter(Builder $query, array $filters)
    {
        return $query
            ->when($filters['title'] ?? null, fn ($q, $val) => $q->where('title', 'like', "%$val%")
            )
            ->when($filters['url'] ?? null, fn ($q, $val) => $q->where('url', 'like', "%$val%")
            )
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($q) => $q->where('is_active', (bool) $filters['is_active'])
            )
            ->when(isset($filters['show_in_first_page']) && $filters['show_in_first_page'] !== '', fn ($q) => $q->where('show_in_first_page', (bool) $filters['show_in_first_page'])
            )
            ->when($filters['category_url'] ?? null, function ($q, $url) {
                $q->whereHas('category', function ($subQ) use ($url) {
                    $subQ->where('url', $url);
                });
            })
            ->when($filters['start_date'] ?? null, function ($q, $date) {
                $jDate = explode('/', $date);
                if (count($jDate) === 3) {
                    $start = jalali_to_gregorian($jDate[0], $jDate[1], $jDate[2], '-');
                    $q->where('created_at', '>=', $start.' 00:00:00');
                }
            })
            ->when($filters['end_date'] ?? null, function ($q, $date) {
                $jDate = explode('/', $date);
                if (count($jDate) === 3) {
                    $end = jalali_to_gregorian($jDate[0], $jDate[1], $jDate[2], '-');
                    $q->where('created_at', '<', $end.' 23:59:59');
                }
            });
    }
}
