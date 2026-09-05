<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Table `tag` from prisma/schema.prisma (model Tag).
 */
class Tag extends BaseModel
{
    public $timestamps = false;

    protected $table = 'tag';

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(BlogPost::class, '_blogposttotag', 'B', 'A');
    }
}
