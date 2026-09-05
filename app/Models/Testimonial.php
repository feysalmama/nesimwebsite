<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `testimonial` from prisma/schema.prisma (model Testimonial).
 */
class Testimonial extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'testimonial';

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
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
