<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `newscategory` from prisma/schema.prisma (model NewsCategory).
 */
class NewsCategory extends BaseModel
{
    public $timestamps = false;

    protected $table = 'newscategory';

    public function posts(): HasMany
    {
        return $this->hasMany(NewsPost::class, 'newsCategoryId');
    }
}
