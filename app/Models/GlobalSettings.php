<?php

namespace App\Models;

/**
 * Singleton row, always id = "site-settings". Ported from lib/content.ts:
 * getSettings() is read-only and falls back to hardcoded defaults when the row
 * is missing, because a fresh database has not been seeded yet.
 */
class GlobalSettings extends BaseModel
{
    const CREATED_AT = null;

    protected $table = 'globalsettings';

    public const SINGLETON_ID = 'site-settings';

    /**
     * Mirrors FALLBACK_SETTINGS in lib/content.ts and the column defaults in
     * prisma/schema.prisma.
     */
    public const FALLBACKS = [
        'id' => self::SINGLETON_ID,
        'orgName' => 'Nesim',
        'shortName' => 'Nesim',
        'primaryColor' => '#0F4C2A',
        'accentColor' => '#E8721C',
    ];

    protected function casts(): array
    {
        return ['updatedAt' => 'datetime'];
    }

    /**
     * Resolved once per request: the navbar, footer and every page need it.
     */
    protected static ?self $resolved = null;

    /**
     * Never returns null — an unseeded database still renders the site.
     */
    public static function current(): self
    {
        if (static::$resolved === null) {
            static::$resolved = static::query()->find(self::SINGLETON_ID)
                ?? (new static)->forceFill(self::FALLBACKS);
        }

        return static::$resolved;
    }

    /**
     * Call after saving settings so the next render in the same request sees
     * the new values (and so long-running workers never serve stale ones).
     */
    public static function flushResolved(): void
    {
        static::$resolved = null;
    }

    public function logoOrDefault(): string
    {
        return $this->logoUrl ?: '/logo.png';
    }
}
