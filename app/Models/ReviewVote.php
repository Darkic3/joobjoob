<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewVote extends Model
{
    protected $fillable = [
        'review_id',
        'user_id',
        'type',
    ];

    protected $casts = [
        // no special cast needed; 'type' is already an enum string
    ];

    const TYPE_LIKE    = 'like';
    const TYPE_DISLIKE = 'dislike';

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
