<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `projectimage` from prisma/schema.prisma (model ProjectImage).
 */
class ProjectImage extends BaseModel
{
    public $timestamps = false;

    protected $table = 'projectimage';

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'projectId');
    }
}
