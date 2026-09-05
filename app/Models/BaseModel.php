<?php

namespace App\Models;

use App\Support\LocaleText;
use Illuminate\Database\Eloquent\Model;

/**
 * Base for every model mapped onto the existing Prisma/MySQL schema.
 *
 * Three things differ from Laravel's defaults and apply to all 34 tables:
 *
 *  1. Primary keys are cuid() strings ("cmtnd21rz0001583nwohkkhcs"), not
 *     auto-incrementing bigints.
 *  2. Timestamp columns are camelCase: createdAt / updatedAt, datetime(3).
 *     Not every table has both, so subclasses null out the missing one.
 *  3. Table names are all-lowercase. MySQL on Windows folds identifiers
 *     (lower_case_table_names=1), so Prisma's model HeroSlide lives in the
 *     table "heroslide". Eloquent would guess "hero_slides", hence every
 *     subclass declares $table explicitly.
 *
 * Because the keys are strings, Prisma generated them client-side with
 * @default(cuid()) and MySQL has no such default. Eloquent therefore fills them
 * itself — see booted() below — or every INSERT would write an empty primary key.
 *
 * Mass assignment is left open ($guarded = []) to match the behaviour of the
 * Next.js admin API, which passed raw request JSON straight to Prisma. Public
 * submission forms must validate before saving — never bind request input
 * directly to these models.
 */
abstract class BaseModel extends Model
{
    const CREATED_AT = 'createdAt';

    const UPDATED_AT = 'updatedAt';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    /** Assign a cuid primary key on insert unless the caller supplied one. */
    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $key = $model->getKeyName();

            if (! $model->getAttribute($key)) {
                $model->setAttribute($key, self::cuid());
            }
        });
    }

    /**
     * A cuid-shaped identifier: "c" followed by 24 lowercase base36 characters,
     * matching the length and charset of Prisma's @default(cuid()).
     *
     * This is not the cuid algorithm itself, which folds in a per-process
     * counter and a fingerprint to make collisions impossible across clients.
     * Here 24 characters drawn from ~124 bits of random_bytes() entropy is far
     * past what this site's write volume can exhaust, and staying format-
     * compatible is what matters — the ids share columns with Prisma's.
     */
    public static function cuid(): string
    {
        static $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';

        $id = 'c';
        $bytes = random_bytes(24);

        for ($i = 0; $i < 24; $i++) {
            // Modulo bias is immaterial for an identifier, so the cheap form is
            // used rather than rejection sampling.
            $id .= $alphabet[ord($bytes[$i]) % 36];
        }

        return $id;
    }

    /**
     * Drop any per-request cache of this table's contents.
     *
     * A no-op for all but one model. GlobalSettings overrides it, because it
     * resolves the singleton once per request for the navbar and footer and the
     * CMS has to be able to invalidate that the moment it saves.
     *
     * Declared here rather than checked for with method_exists() so the admin
     * editor can call it unconditionally and a future cached model is picked up
     * by overriding one method instead of by being remembered somewhere else.
     */
    public static function flushResolved(): void
    {
        //
    }

    /**
     * Read a locale-JSON column for the active language.
     *
     * In Blade: {{ $slide->text('title') }} instead of repeating the helper.
     */
    public function text(string $field, ?string $locale = null, string $fallback = ''): string
    {
        $value = $this->getAttribute($field);

        return LocaleText::get(is_string($value) ? $value : null, $locale, $fallback);
    }

    /**
     * The three-language editing shape for a locale-JSON column, used by the
     * CMS forms.
     *
     * @return array{en: string, am: string, om: string}
     */
    public function locale(string $field): array
    {
        $value = $this->getAttribute($field);

        return LocaleText::parse(is_string($value) ? $value : null);
    }
}
