<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `blogcategory` from prisma/schema.prisma (model BlogCategory).
 */
class BlogCategory extends BaseModel
{
    public $timestamps = false;

    protected $table = 'blogcategory';

    public function posts(): HasMany
    {
        return $this->hasMany(BlogPost::class, 'categoryId');
    }
}
