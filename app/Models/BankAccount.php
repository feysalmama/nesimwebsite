<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * Table `bankaccount`, the one table Laravel created rather than Prisma — see
 * its migration for why it is shaped exactly like `partner`.
 */
class BankAccount extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'bankaccount';

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
