<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `project` from prisma/schema.prisma (model Project).
 */
class Project extends BaseModel
{
    protected $table = 'project';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'featured' => 'boolean',
            'order' => 'integer',
            'startDate' => 'datetime',
            'endDate' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'categoryId');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class, 'projectId');
    }

    /**
     * Only published rows reach the public site (lib/content.ts).
     * getProjects() orders by [{ order: asc }, { createdAt: desc }] — the second
     * key breaks ties between projects an editor left at the same order value.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('order')->orderByDesc('createdAt');
    }
}
