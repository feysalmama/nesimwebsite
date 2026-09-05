<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `impactstat` from prisma/schema.prisma (model ImpactStat).
 */
class ImpactStat extends BaseModel
{
    public $timestamps = false;

    protected $table = 'impactstat';

    protected function casts(): array
    {
        return [
            'value' => 'integer',
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('order');
    }
}
