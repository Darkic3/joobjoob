<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'guest_name',
        'guest_phone',
        'rating',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'integer',
    ];

    // مقادیر is_approved
    const STATUS_PENDING  = 0;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', self::STATUS_PENDING);
    }

    public function getIsApprovedLabelAttribute(): string
    {
        return match ($this->is_approved) {
            self::STATUS_APPROVED => __('admin.reviews.status_approved'),
            self::STATUS_REJECTED => __('admin.reviews.status_rejected'),
            default               => __('admin.reviews.status_pending'),
        };
    }

    /**
     * نام نمایشی نویسنده نظر — کاربر ثبت‌نام‌شده یا مهمان.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        return $this->guest_name ?: __('messages.user');
    }

    /**
     * حرف اول نام برای آواتار.
     */
    public function getAvatarLetterAttribute(): string
    {
        return mb_substr($this->display_name, 0, 1);
    }
}
