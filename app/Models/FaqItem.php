<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `faqitem` from prisma/schema.prisma (model FaqItem).
 */
class FaqItem extends BaseModel
{
    public $timestamps = false;

    protected $table = 'faqitem';

    protected function casts(): array
    {
        return [
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
