<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemError extends Model
{
    protected $fillable = [
        'level',
        'exception_class',
        'message',
        'file',
        'line',
        'trace',
        'context',
        'url',
        'method',
        'ip',
        'user_id',
        'is_resolved',
        'resolved_note',
    ];

    protected $casts = [
        'trace'       => 'array',
        'context'     => 'array',
        'is_resolved' => 'boolean',
    ];

    // ==================== Relationships ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scopes ====================

    public function scopeByLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->where('is_resolved', false);
    }

    public function scopeRecent(Builder $query, int $hours = 24): Builder
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    public function scopeByException(Builder $query, string $exceptionClass): Builder
    {
        return $query->where('exception_class', $exceptionClass);
    }

    // ==================== Helpers ====================

    public function markResolved(?string $note = ''): void
    {
        $this->update([
            'is_resolved'   => true,
            'resolved_note' => $note ?? '',
        ]);
    }

    public function getLevelBadgeAttribute(): string
    {
        return match ($this->level) {
            'critical' => 'danger',
            'error'    => 'warning',
            'warning'  => 'info',
            default    => 'secondary',
        };
    }

    public function getShortMessageAttribute(): string
    {
        return mb_substr($this->message, 0, 120) . (mb_strlen($this->message) > 120 ? '…' : '');
    }

    public function getShortFileAttribute(): string
    {
        if (! $this->file) {
            return '—';
        }

        return basename($this->file) . ':' . ($this->line ?? '?');
    }
}
