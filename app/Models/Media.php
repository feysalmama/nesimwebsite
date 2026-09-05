<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'media';

    protected function casts(): array
    {
        return [
            'fileSize' => 'integer',
            'createdAt' => 'datetime',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploadedBy');
    }

    public function isVideo(): bool
    {
        return $this->fileType === 'video';
    }
}
