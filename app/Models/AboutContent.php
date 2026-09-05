<?php

namespace App\Models;

/**
 * Table `aboutcontent` from prisma/schema.prisma (model AboutContent).
 *
 * Singleton: Prisma gave the id a literal default of "about-content" and MySQL
 * has no such default, so the row is addressed by that constant everywhere
 * rather than by a query that could pick up a second one.
 */
class AboutContent extends BaseModel
{
    const CREATED_AT = null;

    protected $table = 'aboutcontent';

    public const SINGLETON_ID = 'about-content';

    /**
     * The one row, or an unsaved instance carrying its id so an editor's first
     * save inserts rather than failing to find anything to update.
     */
    public static function current(): self
    {
        return static::query()->firstOrNew(['id' => self::SINGLETON_ID]);
    }
}
