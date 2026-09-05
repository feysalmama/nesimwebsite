<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `partner` from prisma/schema.prisma (model Partner).
 */
class Partner extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'partner';

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('order');
    }
}
