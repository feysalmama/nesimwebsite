<?php

namespace App\Support;

/**
 * Content fields (title, body, summary, ...) are stored in the database as a
 * JSON string: {"en":"...","am":"...","om":"..."}. That lets an editor manage
 * all three languages for one record in a single CMS form.
 *
 * This is a direct port of lib/locale-content.ts from the Next.js app, so the
 * same rows render identically. Note it deliberately tolerates three shapes:
 * a proper locale object, a doubly-encoded object (some legacy rows hold a
 * JSON string *inside* the JSON), and plain text with no JSON at all.
 */
class LocaleText
{
    public const LOCALES = ['en', 'am', 'om'];

    public const DEFAULT_LOCALE = 'en';

    public const LABELS = [
        'en' => 'English',
        'am' => 'አማርኛ',
        'om' => 'Afaan Oromoo',
    ];

    /**
     * Resolve a stored field to the string for the active locale, falling back
     * to English and then to the caller's default.
     */
    public static function get(?string $field, ?string $locale = null, string $fallback = ''): string
    {
        if ($field === null || $field === '') {
            return $fallback;
        }

        $locale = $locale ?: app()->getLocale();

        $decoded = json_decode($field, true);

        if (! is_array($decoded)) {
            // Not JSON at all — plain text stored directly in the column.
            return $field;
        }

        $raw = $decoded[$locale] ?? $decoded[self::DEFAULT_LOCALE] ?? $fallback;

        if (! is_string($raw)) {
            return is_scalar($raw) ? (string) $raw : $fallback;
        }

        // Legacy rows occasionally hold a second layer of JSON encoding.
        if (str_starts_with($raw, '{')) {
            $inner = json_decode($raw, true);

            if (is_array($inner)) {
                $value = $inner[$locale] ?? $inner[self::DEFAULT_LOCALE] ?? $raw;

                return is_string($value) ? $value : $raw;
            }
        }

        return $raw;
    }

    /**
     * Split a stored field back into the three-language editing shape used by
     * the CMS forms (port of parseLocaleJson).
     *
     * @return array{en: string, am: string, om: string}
     */
    public static function parse(?string $field): array
    {
        if ($field === null || $field === '') {
            return ['en' => '', 'am' => '', 'om' => ''];
        }

        $decoded = json_decode($field, true);

        if (! is_array($decoded)) {
            return ['en' => $field, 'am' => '', 'om' => ''];
        }

        return [
            'en' => (string) ($decoded['en'] ?? ''),
            'am' => (string) ($decoded['am'] ?? ''),
            'om' => (string) ($decoded['om'] ?? ''),
        ];
    }

    /**
     * Re-encode the three languages for storage (port of makeLocaleJson).
     */
    public static function encode(string $en, string $am = '', string $om = ''): string
    {
        return (string) json_encode(
            ['en' => $en, 'am' => $am, 'om' => $om],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Decode a plain JSON array column (communityImages, impactStats,
     * factsItems, reachRegions, processSteps, storiesItems, timelineData).
     * Returns [] rather than throwing when the column is null or malformed.
     */
    public static function json(?string $field): array
    {
        if ($field === null || $field === '') {
            return [];
        }

        $decoded = json_decode($field, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Resolve a value that has already been decoded, rather than a column.
     *
     * The documents inside a JSON array column (a timeline entry, a story, a
     * process step) hold their translatable fields as locale maps —
     * ['en' => …, 'am' => …, 'om' => …] — not as encoded strings, so get()
     * cannot be pointed at them. Anything scalar is passed through get() so a
     * plain string, an encoded document and a doubly-encoded one all behave the
     * same way here as they do everywhere else.
     */
    public static function pick(mixed $value, ?string $locale = null, string $fallback = ''): string
    {
        $locale = $locale ?: app()->getLocale();

        if (is_array($value)) {
            $raw = $value[$locale] ?? $value[self::DEFAULT_LOCALE] ?? $fallback;

            return is_scalar($raw) ? (string) $raw : $fallback;
        }

        if ($value === null || ! is_scalar($value)) {
            return $fallback;
        }

        return self::get((string) $value, $locale, $fallback);
    }
}
