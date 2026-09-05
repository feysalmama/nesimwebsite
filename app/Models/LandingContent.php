<?php

namespace App\Models;

/**
 * Table `landingcontent` from prisma/schema.prisma (model LandingContent).
 *
 * Singleton, addressed by the literal id Prisma defaulted it to. Several of its
 * columns hold JSON arrays the home page reads directly — communityImages,
 * impactStats, factsItems, reachRegions, processSteps, storiesItems — which the
 * CMS edits through repeater fields rather than as raw text.
 */
class LandingContent extends BaseModel
{
    const CREATED_AT = null;

    protected $table = 'landingcontent';

    public const SINGLETON_ID = 'landing-content';

    /** The one row, or an unsaved instance carrying its id for a first save. */
    public static function current(): self
    {
        return static::query()->firstOrNew(['id' => self::SINGLETON_ID]);
    }
}
