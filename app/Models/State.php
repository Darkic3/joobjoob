<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class State extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'status', 'cod24_id', 'chapar_id'];

    protected $casts = [
        'status' => 'integer',
        'cod24_id' => 'integer',
        'chapar_id' => 'integer',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
