<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'address_id', 'state_id', 'city_id', 'address',
        'receiptor_full_name', 'shipping_status_id', 'order_status',
        'freight_balance', 'basket_id', 'bank_id', 'shipping_method_id',
        'discount_id', 'total_weight', 'post_code',
        'shipping_price', 'total_price', 'discount_price', 'payment_price',
        'tax_price', 'current_tax', 'deposit_price', 'remaining_price',
        'user_description', 'transaction_info',
        'receipt_tracking_code', 'receipt_payer_card',
    ];

    protected $casts = [
        'address' => 'array',
        'transaction_info' => 'array',
        'freight_balance' => 'integer',
        'total_weight' => 'double',
    ];

    /**
     * حذف فرمت از شماره کارت واریزکننده هنگام ذخیره در دیتابیس
     */
    public function setReceiptPayerCardAttribute(?string $value): void
    {
        $this->attributes['receipt_payer_card'] = clean_card_number($value);
    }

    // ==================== Scopes ====================

    public function scopeAuthUser($query)
    {
        return $query->where('user_id', Auth::id());
    }

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function shippingStatus(): BelongsTo
    {
        return $this->belongsTo(OrderShippingStatus::class, 'shipping_status_id');
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'shipping_method_id');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)
            ->where('quantity', '>', 0)
            ->with('product');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(OrderItem::class)->with('product');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(OrderHistory::class)->latest();
    }

    // ==================== Accessors ====================

    public function getDateAttribute(): ?string
    {
        return $this->created_at?->format('d/m/Y');
    }

    public function getTimeAttribute(): ?string
    {
        return $this->created_at?->format('H:i');
    }

    public function getStatusAttribute(): ?array
    {
        return match ($this->order_status) {
            'paying' => ['title' => 'در حال پرداخت', 'badge' => 'warning'],
            'paid' => ['title' => 'پرداخت شده', 'badge' => 'success'],
            'deposit_paid' => ['title' => 'بیعانه پرداخت شده', 'badge' => 'success'],
            'wait_for_verification' => ['title' => 'در انتظار تایید فیش ها', 'badge' => 'info'],
            'pending_confirmation' => ['title' => 'در انتظار تأیید پرداخت', 'badge' => 'warning'],
            'unpaid' => ['title' => 'پرداخت نشده', 'badge' => 'danger'],
            'cancelled' => ['title' => 'لغو شده', 'badge' => 'danger'],
            default => null,
        };
    }

    public function getShippingNameAttribute(): string
    {
        $shippingPrice = intval($this->shipping_price);
        $freightBalance = $this->freight_balance;

        if ($freightBalance === 1) {
            return 'پس کرایه';
        }

        if ($shippingPrice === 0) {
            return 'ارسال رایگان';
        }

        return number_format($shippingPrice) . ' تومان';
    }

    public function getFreightBalanceNameAttribute(): array
    {
        return $this->attributes['freight_balance'] == 1
            ? ['title' => 'پس کرایه', 'badge' => 'success']
            : ['title' => 'پیش کرایه', 'badge' => 'info'];
    }

    public function getBankTrackingCodeAttribute(): string
    {
        $transaction = $this->transaction_info ?? [];
        $bankType = $this->bank?->bank_type;

        // کارت به کارت — کد پیگیری وارد شده توسط کاربر
        if ($bankType === 'card_to_card' && $this->receipt_tracking_code) {
            return $this->receipt_tracking_code;
        }

        return match ($bankType) {
            'sep' => $transaction['verify']['TraceNo'] ?? '',
            'zarinPal' => $transaction['verify']['RefID'] ?? '',
            'sadad' => $transaction['verify']['RetrivalRefNo'] ?? '',
            default => '',
        };
    }

    public function getIsCardToCardAttribute(): bool
    {
        return in_array($this->bank?->bank_type ?? '', ['card_to_card', 'card-to-card']);
    }

    // ==================== Methods ====================

    public function hasReturnItem(): ?string
    {
        $hasOld = $this->allItems()->whereNotNull('old_quantity')->exists();

        return $hasOld
            ? '<span class="badge bg-label-danger">دارای مرجوعی</span>'
            : null;
    }
}
