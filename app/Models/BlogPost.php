<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Table `blogpost` from prisma/schema.prisma (model BlogPost).
 */
class BlogPost extends BaseModel
{
    protected $table = 'blogpost';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'featured' => 'boolean',
            'publishedAt' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorId');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'categoryId');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, '_blogposttotag', 'A', 'B');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
