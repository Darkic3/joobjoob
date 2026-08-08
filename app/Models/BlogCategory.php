<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parent_id', 'title', 'slug', 'description', 'image',
        'is_active', 'show_in_first_page', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_in_first_page' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    // Relations
    public function parent(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(BlogCategory::class, 'parent_id');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    // Scopes (for clean queries in controller)
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // URL تصویر — تصاویر توسط ImageUploader در public/uploads/blog-categories ذخیره می‌شوند
    public function getImageUrlAttribute(): ?string
    {
        return $this->image
            ? asset(config('paths.upload', 'uploads').'/blog-categories/'.$this->image)
            : null;
    }
}
