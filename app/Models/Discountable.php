<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discountable extends Model
{
    public $timestamps = false;

    protected $fillable = ['discount_id', 'discountable_id', 'discountable_type'];

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function discountable()
    {
        return $this->morphTo();
    }
}
