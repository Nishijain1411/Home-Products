<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $fillable = [
        'name',
        'settings',
        'published_settings',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'published_settings' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
