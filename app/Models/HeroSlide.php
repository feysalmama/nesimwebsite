<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class HeroSlide extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'heroslide';

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
            'createdAt' => 'datetime',
        ];
    }

    /** getHeroSlides() in lib/content.ts */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('order');
    }
}
