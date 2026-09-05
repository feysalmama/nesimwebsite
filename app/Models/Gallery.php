<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Table `gallery` from prisma/schema.prisma (model Gallery).
 */
class Gallery extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'gallery';

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'eventDate' => 'datetime',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'galleryId');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
