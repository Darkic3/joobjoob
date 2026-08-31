<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'user_id',
        'parent_id',
        'guest_name',
        'guest_phone',
        'rating',
        'comment',
        'is_approved',
        'is_featured',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'integer',
        'is_featured' => 'boolean',
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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Review::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Review::class, 'parent_id')->approved()->oldest();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class);
    }

    /**
     * رأی کاربر فعلی روی این نظر (اگر لاگین باشد).
     */
    public function currentUserVote(): HasOne
    {
        return $this->hasOne(ReviewVote::class)->where('user_id', auth()->id());
    }

    /**
     * تعداد لایک‌ها
     */
    public function getLikesCountAttribute(): int
    {
        return $this->votes()->where('type', ReviewVote::TYPE_LIKE)->count();
    }

    /**
     * تعداد دیسلاک‌ها
     */
    public function getDislikesCountAttribute(): int
    {
        return $this->votes()->where('type', ReviewVote::TYPE_DISLIKE)->count();
    }

    /**
     * آیا این نظر یک پاسخ است؟
     */
    public function getIsReplyAttribute(): bool
    {
        return $this->parent_id !== null;
    }

    public function scopeApproved($query)
    {
        return $query->where('is_approved', self::STATUS_APPROVED);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
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
