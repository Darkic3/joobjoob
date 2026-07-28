<?php

namespace App\Traits;

trait HasJalaliDate
{
    public function getJalaliCreatedAtAttribute(): array|string|null
    {
        if ($this->created_at) {
            return jdate_format('d F Y', strtotime($this->created_at));
        }
        return null;
    }
}
