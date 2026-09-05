<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `service` from prisma/schema.prisma (model Service).
 */
class Service extends BaseModel
{
    protected $table = 'service';

    protected function casts(): array
    {
        return [
            'featured' => 'boolean',
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
