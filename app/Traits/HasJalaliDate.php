<?php

namespace App\Traits;

trait HasJalaliDate
{
    public function getJalaliCreatedAtAttribute(): array|string|null
    {
        if ($this->created_at) {
            // بر اساس زبان سایت: fa/ar → شمسی، سایر → میلادی
            return show_date($this->created_at, 'd F Y');
        }

        return null;
    }
}
