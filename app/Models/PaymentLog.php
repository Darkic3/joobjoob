<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    protected $fillable = [
        'order_id',
        'gateway',
        'action',
        'status',
        'request_payload',
        'response_payload',
        'error_message',
        'amount',
        'reference_id',
        'ip',
        'response_time_ms',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
        'response_time_ms' => 'integer',
        'amount'           => 'integer',
    ];

    // ==================== Scopes ====================

    public function scopeByGateway(Builder $query, string $gateway): Builder
    {
        return $query->where('gateway', $gateway);
    }

    public function scopeByOrder(Builder $query, int $orderId): Builder
    {
        return $query->where('order_id', $orderId);
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
            'success' => ['label' => 'موفق', 'color' => 'success'],
            'failed'  => ['label' => 'ناموفق', 'color' => 'danger'],
            'pending' => ['label' => 'در انتظار', 'color' => 'warning'],
            default   => ['label' => $this->status, 'color' => 'secondary'],
        };
    }

    public function getHumanActionAttribute(): string
    {
        return match ($this->action) {
            'token_request' => 'درخواست توکن',
            'verify'        => 'تأیید تراکنش',
            'callback'      => 'برگشت از درگاه',
            default         => $this->action,
        };
    }

    public function getHumanGatewayAttribute(): string
    {
        return match ($this->gateway) {
            'zarinpal' => 'زرین‌پال',
            'saman'    => 'سامان',
            'sadad'    => 'سداد',
            'zibal'    => 'زیبال',
            default    => $this->gateway,
        };
    }
}
