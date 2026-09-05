<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `membershipapplication` from prisma/schema.prisma (model MembershipApplication).
 */
class MembershipApplication extends BaseModel
{
    protected $table = 'membershipapplication';

    protected function casts(): array
    {
        return [
            'dob' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MembershipCategory::class, 'categoryId');
    }
}
