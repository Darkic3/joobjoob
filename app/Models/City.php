<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class City extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'status', 'state_id', 'cod24_id', 'chapar_id'];

    protected $casts = [
        'status' => 'integer',
        'state_id' => 'integer',
        'cod24_id' => 'integer',
        'chapar_id' => 'integer',
    ];

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function shippingMethods(): BelongsToMany
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_city');
    }

    public function availableShippingMethods(): BelongsToMany
    {
        return $this->belongsToMany(ShippingMethod::class, 'shipping_method_city')
            ->where('shipping_methods.status', 1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
