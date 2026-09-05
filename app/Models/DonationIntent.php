<?php

namespace App\Models;

/**
 * Table `donationintent` from prisma/schema.prisma (model DonationIntent).
 */
class DonationIntent extends BaseModel
{
    const UPDATED_AT = null;

    protected $table = 'donationintent';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }
}
