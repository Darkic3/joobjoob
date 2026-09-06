<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'event',
        'auditable_type',
        'auditable_id',
        'user_id',
        'old_values',
        'new_values',
        'meta',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'meta' => 'array',
    ];

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    // ==================== Scopes ====================

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('auditable_type', Order::class)
            ->where('auditable_id', $orderId);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('auditable_type', Product::class)
            ->where('auditable_id', $productId);
    }

    public function scopeEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    public function scopeEvents($query, array $events)
    {
        return $query->whereIn('event', $events);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==================== Helpers ====================

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'order.created' => 'ایجاد سفارش',
            'order.receipt_updated' => 'ویرایش اطلاعات فیش واریزی',
            'order.status_changed' => 'تغییر وضعیت سفارش',
            'order.shipping_status_changed' => 'تغییر وضعیت ارسال',
            'order.cancelled' => 'لغو سفارش',
            'order.payment_success' => 'پرداخت موفق',
            'order.payment_failed' => 'پرداخت ناموفق',
            'order.stock_reserved' => 'رزرو موجودی',
            'order.stock_restored' => 'بازگردانی موجودی',
            'product.created' => 'ایجاد محصول',
            'product.updated' => 'ویرایش محصول',
            'product.price_changed' => 'تغییر قیمت محصول',
            'product.stock_changed' => 'تغییر موجودی محصول',
            'product.stock_added' => 'افزایش موجودی محصول',
            'product.stock_deducted' => 'کاهش موجودی محصول',
            default => $this->event,
        };
    }

    public function getEventBadgeAttribute(): string
    {
        return match (true) {
            str_contains($this->event, 'created') => 'success',
            str_contains($this->event, 'cancelled') || str_contains($this->event, 'failed') => 'danger',
            str_contains($this->event, 'changed') || str_contains($this->event, 'updated') => 'warning',
            str_contains($this->event, 'reserved') || str_contains($this->event, 'restored') => 'info',
            str_contains($this->event, 'stock_added') => 'success',
            str_contains($this->event, 'stock_deducted') => 'warning',
            default => 'secondary',
        };
    }
}
