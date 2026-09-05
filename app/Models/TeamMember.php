<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `teammember` from prisma/schema.prisma (model TeamMember).
 */
class TeamMember extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'teammember';

    protected function casts(): array
    {
        return [
            'isLeader' => 'boolean',
            'order' => 'integer',
            'published' => 'boolean',
        ];
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('order');
    }
}
