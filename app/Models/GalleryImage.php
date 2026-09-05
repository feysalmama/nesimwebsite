<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `galleryimage` from prisma/schema.prisma (model GalleryImage).
 */
class GalleryImage extends BaseModel
{
    public $timestamps = false;

    protected $table = 'galleryimage';

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class, 'galleryId');
    }
}
