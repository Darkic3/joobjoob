<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RequestPerformanceLog extends Model
{
    protected $fillable = [
        'method',
        'path',
        'status_code',
        'response_time_ms',
        'ip',
        'user_id',
        'is_slow',
    ];

    protected $casts = [
        'response_time_ms' => 'integer',
        'is_slow'          => 'boolean',
    ];

    // ==================== Scopes ====================

    public function scopeSlow(Builder $query): Builder
    {
        return $query->where('is_slow', true);
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // ==================== Helpers ====================

    public function getResponseTimeLabelAttribute(): string
    {
        $ms = $this->response_time_ms;

        if ($ms < 500) {
            return 'سریع';
        }

        if ($ms < 1000) {
            return 'نرمال';
        }

        if ($ms < 2000) {
            return 'کند';
        }

        if ($ms < 5000) {
            return 'خیلی کند';
        }

        return 'بحرانی';
    }

    public function getResponseTimeColorAttribute(): string
    {
        $ms = $this->response_time_ms;

        if ($ms < 500) {
            return 'success';
        }

        if ($ms < 1000) {
            return 'info';
        }

        if ($ms < 2000) {
            return 'warning';
        }

        return 'danger';
    }
}
