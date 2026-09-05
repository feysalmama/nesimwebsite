<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Table `newspost` from prisma/schema.prisma (model NewsPost).
 */
class NewsPost extends BaseModel
{
    protected $table = 'newspost';

    /*
     * `category` is a plain string column with a database default of "news", not
     * a foreign key to newscategory - the React pages compared it against string
     * literals, and news/page.tsx and insights/page.tsx between them decided that
     * three values exist. Named here because two controllers read them and a
     * typo in either would silently empty a page rather than fail loudly.
     */
    public const CATEGORY_NEWS = 'news';

    public const CATEGORY_MEDIA = 'media';

    public const CATEGORY_INSIGHT = 'insight';

    /** What the News & Media page shows. Insights shows the third. */
    public const FEED_CATEGORIES = [self::CATEGORY_NEWS, self::CATEGORY_MEDIA];

    protected function casts(): array
    {
        return [
            'published' => 'boolean',
            'featured' => 'boolean',
            'publishedAt' => 'datetime',
        ];
    }

    public function newsCategory(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'newsCategoryId');
    }

    /** Only published rows reach the public site (lib/content.ts). */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
