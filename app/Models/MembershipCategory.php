<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `membershipcategory` from prisma/schema.prisma (model MembershipCategory).
 */
class MembershipCategory extends BaseModel
{
    public $timestamps = false;

    protected $table = 'membershipcategory';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(MembershipApplication::class, 'categoryId');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->orderBy('order');
    }
}
