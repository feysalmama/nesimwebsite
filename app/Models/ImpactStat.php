<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `impactstat` from prisma/schema.prisma (model ImpactStat).
 */
class ImpactStat extends BaseModel
{
    /**
     * The numbers the site shows when this table is empty, so a fresh install
     * still has a credible counters band rather than a gap where the headline
     * figures go. Declared here rather than in a controller because both the
     * homepage and /impact fall back to it, and it describes rows of this table.
     *
     * `label` is plain text rather than a locale document: it is a literal, not
     * a column value, so there is nothing for text() to resolve.
     */
    public const FALLBACK_ROWS = [
        ['id' => 's1', 'value' => 12400, 'suffix' => '+', 'label' => 'Students Reached'],
        ['id' => 's2', 'value' => 86, 'suffix' => '', 'label' => 'Schools Supported'],
        ['id' => 's3', 'value' => 340, 'suffix' => '+', 'label' => 'Volunteers Engaged'],
        ['id' => 's4', 'value' => 9, 'suffix' => '', 'label' => 'Regions Active'],
    ];

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
