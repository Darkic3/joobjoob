<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = ['name', 'slug', 'preview_color', 'is_default', 'css_variables'];

    protected $casts = [
        'is_default' => 'boolean',
        'css_variables' => 'array',
    ];

    public function getMergedCssVariables(): array
    {
        $defaults = [
            '--bg' => '#e0e5ec',
            '--bg-elevated' => '#e0e5ec',
            '--shadow-light' => '#ffffff',
            '--shadow-dark' => '#a3b1c6',
            '--text-primary' => '#2d3748',
            '--text-secondary' => '#6b7788',
            '--accent' => '#6c63ff',
            '--accent-contrast' => '#ffffff',
            '--danger' => '#e53e5c',
            '--success' => '#38a169',
        ];

        return array_merge($defaults, $this->css_variables ?? []);
    }
}
