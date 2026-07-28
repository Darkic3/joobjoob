<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'blog_category_id', 'user_id', 'title', 'slug', 'excerpt',
        'content', 'image', 'views', 'is_active', 'show_in_first_page'
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
            'is_active' => 'boolean',
            'show_in_first_page' => 'boolean',
        ];
    }

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFirstPage($query)
    {
        return $query->where('show_in_first_page', true);
    }

    // Increase view method
    public function incrementViews(): void
    {
        $this->increment('views');
    }
}
