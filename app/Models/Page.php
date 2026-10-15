<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'template',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'canonical_url',
        'is_indexed',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_indexed' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)
            ->orWhere('slug', $value)
            ->firstOrFail();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getSeoTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function getSeoDescription(): string
    {
        if (! empty($this->meta_description)) {
            return $this->meta_description;
        }

        return Str::limit(strip_tags((string) $this->content), 160);
    }

    public function getCanonicalUrl(): string
    {
        if (! empty($this->canonical_url)) {
            return $this->canonical_url;
        }

        try {
            return route('storefront.pages.show', $this->slug);
        } catch (\Throwable) {
            return url('/page/'.$this->slug);
        }
    }
}
