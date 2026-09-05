<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class IslamicMessage extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'islamicmessage';

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
            'createdAt' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true)->orderBy('order');
    }
}
