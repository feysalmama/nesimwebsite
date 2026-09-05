<?php

use App\Support\LocaleText;

/*
 | Global Blade helpers. Registered through composer.json "autoload.files".
 | These replace the URL building that next-intl's <Link> + usePathname did in
 | the Next.js app, and the t()/safeParse() helpers from lib/locale-content.ts.
 */

if (! function_exists('default_locale')) {
    function default_locale(): string
    {
        return LocaleText::DEFAULT_LOCALE;
    }
}

if (! function_exists('locale_path')) {
    /**
     * Build a locale-prefixed public URL.
     *
     * locale_path()          -> "/en"
     * locale_path('about')   -> "/en/about"
     * locale_path('blog/x', 'am') -> "/am/blog/x"
     */
    function locale_path(string $to = '', ?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $to = trim($to, '/');

        return '/'.$locale.($to === '' ? '' : '/'.$to);
    }
}

if (! function_exists('locale_switch_url')) {
    /**
     * The current path rendered in another locale. The language switcher keeps
     * the visitor on the same page rather than dropping them at the homepage.
     */
    function locale_switch_url(string $locale): string
    {
        $segments = request()->segments();

        if ($segments !== [] && in_array($segments[0], LocaleText::LOCALES, true)) {
            array_shift($segments);
        }

        $path = locale_path(implode('/', $segments), $locale);
        $query = request()->getQueryString();

        return $query ? $path.'?'.$query : $path;
    }
}

if (! function_exists('lt')) {
    /**
     * Resolve a locale-JSON database column to the active language.
     * Port of t() in lib/locale-content.ts.
     */
    function lt(?string $field, ?string $locale = null, string $fallback = ''): string
    {
        return LocaleText::get($field, $locale, $fallback);
    }
}

if (! function_exists('lt_json')) {
    /**
     * Decode a plain JSON array column (reachRegions, factsItems, ...).
     * Port of the safeParse() closure in app/[locale]/page.tsx.
     */
    function lt_json(?string $field): array
    {
        return LocaleText::json($field);
    }
}

if (! function_exists('lt_pick')) {
    /**
     * Resolve one field of a decoded JSON document — a timeline entry, a story,
     * a process step — where the value is a locale map rather than an encoded
     * column. Accepts a scalar too, so a view can use it without knowing which
     * shape the editor happened to save.
     */
    function lt_pick(mixed $value, ?string $locale = null, string $fallback = ''): string
    {
        return LocaleText::pick($value, $locale, $fallback);
    }
}

if (! function_exists('format_date')) {
    /**
     * The React pages used new Date(x).toLocaleDateString(locale). Carbon can do
     * the same, but only for locales it actually ships: 'am' exists, 'om' does
     * not, and Carbon::setLocale() throws on an unknown one. So the translated
     * form is attempted and a stable English form is the fallback — a broken
     * date must never take down a page.
     */
    function format_date(mixed $date): string
    {
        if (! $date) {
            return '';
        }

        $date = $date instanceof \Illuminate\Support\Carbon
            ? $date
            : \Illuminate\Support\Carbon::parse($date);

        try {
            return $date->copy()->setLocale(app()->getLocale())->isoFormat('ll');
        } catch (\Throwable) {
            return $date->format('M j, Y');
        }
    }
}

if (! function_exists('initials')) {
    /**
     * The "Stories from the Field" avatar falls back to initials when a story
     * has no photo. Mirrors story.name.split(" ").map(n => n[0]).join("").
     */
    function initials(?string $name): string
    {
        $out = '';

        foreach (preg_split('/\s+/', trim((string) $name)) ?: [] as $part) {
            if ($part !== '') {
                // mb_* because these names are frequently Amharic.
                $out .= mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        return $out;
    }
}
