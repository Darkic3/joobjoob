<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetaTag extends Model
{
//    use HasFactory;
    /**      * The table associated with the model.      *      * @var string */
    protected $table = 'meta_tags';
    /**      * The attributes that are mass assignable.      *      * @var array<int, string> */
    protected $fillable = [
        'title',
        'description',
        'keywords',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'og_url',
        'twitter_card',
        'twitter_site',
        'twitter_creator',
        'twitter_title',
        'twitter_description',
        'twitter_image',
    ];

    /**      * Get the parent meta_taggable model (product, category, etc.).      */
    public function meta_taggable(): MorphTo
    {
        return $this->morphTo();
    }
}
