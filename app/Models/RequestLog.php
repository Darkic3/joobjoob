<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestLog extends Model
{
    protected $fillable = [
        'user_id',
        'method',
        'url',
        'ip',
        'status_code',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * رنگ‌های نشان متد HTTP (برای نمایش در جدول لاگ).
     *
     * @return array{bg: string, text: string}
     */
    public function methodBadge(): array
    {
        return match ($this->method) {
            'GET' => ['bg' => '#07ddef', 'text' => '#373535'],
            'POST' => ['bg' => '#198754', 'text' => '#fff'],
            'PUT', 'PATCH' => ['bg' => '#ffc107', 'text' => '#373535'],
            'DELETE' => ['bg' => '#dc3545', 'text' => '#fff'],
            default => ['bg' => '#6c757d', 'text' => '#fff'],
        };
    }
}
