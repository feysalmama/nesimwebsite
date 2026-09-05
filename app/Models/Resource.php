<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `resource` from prisma/schema.prisma (model Resource).
 */
class Resource extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'resource';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'publishedAt' => 'datetime',
            'downloadCount' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ResourceCategory::class, 'categoryId');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
