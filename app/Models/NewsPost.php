<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `newspost` from prisma/schema.prisma (model NewsPost).
 */
class NewsPost extends BaseModel
{
    protected $table = 'newspost';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'featured' => 'boolean',
            'publishedAt' => 'datetime',
        ];
    }

    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'newsCategoryId');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
