<?php

declare(strict_types=1);

namespace App\Domain\Blog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BlogPost extends Model
{
    protected $fillable = [
        'author_id', 'title', 'slug', 'excerpt', 'content', 'cover_image', 'cover_alt',
        'category', 'tags', 'seo_title', 'seo_description', 'status', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'immutable_datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function publicUrl(): string
    {
        return route('store.blog.show', $this->slug);
    }

    public function summary(): string
    {
        if (filled($this->excerpt)) {
            return (string) $this->excerpt;
        }

        return (string) str(strip_tags((string) $this->content))->squish()->limit(180);
    }
}
