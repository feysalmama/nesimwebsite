<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `program` from prisma/schema.prisma (model Program).
 */
class Program extends BaseModel
{
    protected $table = 'program';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'order' => 'integer',
        ];
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('order');
    }
}
