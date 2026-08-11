<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ContactRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'department',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead(): static
    {
        if ($this->read_at === null) {
            $this->update(['read_at' => now()]);
        }

        return $this;
    }

    public function getIsReadAttribute(): bool
    {
        return $this->read_at !== null;
    }
}
