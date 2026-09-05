<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `resourcecategory` from prisma/schema.prisma (model ResourceCategory).
 */
class ResourceCategory extends BaseModel
{
    public $timestamps = false;

    protected $table = 'resourcecategory';

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class, 'categoryId');
    }
}
