<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'type',
        'template',
        'mobile',
        'status',
        'error_message',
        'user_id',
        'related_id',
        'related_type',
    ];

    // ==================== Scopes ====================

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    // ==================== Helpers ====================

    public function getHumanStatusAttribute(): array
    {
        return match ($this->status) {
            'sent'    => ['label' => 'ارسال شد', 'color' => 'success'],
            'failed'  => ['label' => 'ناموفق', 'color' => 'danger'],
            'skipped' => ['label' => 'رد شد', 'color' => 'secondary'],
            default   => ['label' => $this->status, 'color' => 'secondary'],
        };
    }

    public function getHumanTypeAttribute(): string
    {
        return match ($this->type) {
            'otp'          => 'کد یکبارمصرف',
            'notification' => 'اعلان',
            'welcome'      => 'خوشامدگویی',
            'bulk'         => 'ارسال گروهی',
            default        => $this->type,
        };
    }
}
