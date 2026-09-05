<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `projectcategory` from prisma/schema.prisma (model ProjectCategory).
 */
class ProjectCategory extends BaseModel
{
    public $timestamps = false;

    protected $table = 'projectcategory';

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'categoryId');
    }
}
