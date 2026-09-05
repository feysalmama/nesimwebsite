<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `activitylog` from prisma/schema.prisma (model ActivityLog).
 */
class ActivityLog extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'activitylog';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
