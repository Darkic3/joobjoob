<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrawlLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'products_found',
        'products_imported',
        'products_failed',
        'pages_crawled',
        'limit',
        'status',
        'error_message',
        'started_at',
        'finished_at',
        'duration_seconds',
        'user_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function getDurationAttribute(): ?string
    {
        if (! $this->duration_seconds) {
            return null;
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes} {$seconds}";
        }

        return "$seconds ثانیه";
    }
}
