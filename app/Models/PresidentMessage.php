<?php

namespace App\Models;

/**
 * Table `presidentmessage` from prisma/schema.prisma (model PresidentMessage).
 *
 * Singleton, addressed by the literal id Prisma defaulted it to. The sidebar and
 * the dashboard both call it "Chairman's Message" while the table keeps the
 * original president-message name; the spec carries the public-facing title.
 */
class PresidentMessage extends BaseModel
{
    const CREATED_AT = null;

    protected $table = 'presidentmessage';

    public const SINGLETON_ID = 'president-message';

    /** The one row, or an unsaved instance carrying its id for a first save. */
    public static function current(): self
    {
        return static::query()->firstOrNew(['id' => self::SINGLETON_ID]);
    }
}
